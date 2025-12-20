<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'parent_id',
        'name',
        'code',
        'type',
        'is_payroll',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_payroll' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the property that owns this category.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the parent category (for hierarchical structure).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'parent_id');
    }

    /**
     * Get all child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(FinancialCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (children, grandchildren, etc.) recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all financial entries for this category.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    /**
     * Check if this category has children.
     * Use withCount('children') when querying to avoid N+1 problem.
     */
    public function hasChildren(): bool
    {
        // If children_count is loaded via withCount(), use it
        if (isset($this->children_count)) {
            return $this->children_count > 0;
        }

        // Otherwise, check if children relation is already loaded
        if ($this->relationLoaded('children')) {
            return $this->children->count() > 0;
        }

        // Fallback to query (will cause N+1 if used in loop)
        return $this->children()->count() > 0;
    }

    /**
     * Check if this category is a leaf node (no children).
     */
    public function isLeaf(): bool
    {
        return !$this->hasChildren();
    }

    /**
     * Check if this category should have manual input.
     * Only leaf nodes without code can have manual input.
     */
    public function allowsManualInput(): bool
    {
        return $this->isLeaf() && empty($this->code);
    }

    /**
     * Scope to get only root categories (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Scope to get categories by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get payroll categories.
     */
    public function scopePayroll($query)
    {
        return $query->where('is_payroll', true);
    }

    /**
     * Scope to filter by property.
     */
    public function scopeForProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    /**
     * Get the full hierarchical path of this category.
     * E.g., "Front Office → Payroll & Related Expenses → Salaries & Wages"
     */
    public function getFullPath(): string
    {
        $path = collect([$this->name]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' → ');
    }

    /**
     * Get the root category of this category.
     */
    public function getRootCategory(): ?FinancialCategory
    {
        if (!$this->parent_id) {
            return $this;
        }

        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
        }

        return $current;
    }

    /**
     * Get the depth level of this category (0 for root).
     */
    public function getDepthLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /**
     * Get display name with hierarchical prefix for dropdown.
     * E.g., "└─ └─ Salaries & Wages"
     */
    public function getDisplayNameWithIndent(): string
    {
        $level = $this->getDepthLevel();

        if ($level === 0) {
            return $this->name;
        }

        $prefix = str_repeat('│    ', $level - 1) . '└── ';
        return $prefix . $this->name;
    }

    /**
     * Build a flat list of categories ordered hierarchically for dropdown.
     * Returns array of ['id' => x, 'display' => 'formatted name', 'root' => 'root name']
     */
    public static function getHierarchicalListForDropdown(int $propertyId, ?int $excludeId = null): array
    {
        $categories = static::where('property_id', $propertyId)
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($excludeId) {
            // Get all descendant IDs to exclude
            $excludeIds = static::getDescendantIds($excludeId);
            $excludeIds[] = $excludeId;
            $categories = $categories->reject(fn($cat) => in_array($cat->id, $excludeIds));
        }

        // Build tree structure first
        $rootCategories = $categories->whereNull('parent_id')->sortBy('sort_order');
        $childrenByParent = $categories->whereNotNull('parent_id')->groupBy('parent_id');

        $result = [];

        foreach ($rootCategories as $root) {
            static::addCategoryToList($result, $root, $childrenByParent, 0);
        }

        return $result;
    }

    /**
     * Recursively add category and its children to the flat list.
     */
    private static function addCategoryToList(array &$result, FinancialCategory $category, $childrenByParent, int $level): void
    {
        $indent = $level > 0 ? str_repeat('── ', $level) : '';

        // Get root category name for grouping
        $rootName = $level === 0 ? $category->name : null;
        if ($level > 0) {
            $current = $category;
            while ($current->parent_id) {
                $current = $current->parent;
            }
            $rootName = $current->name;
        }

        $result[] = [
            'id' => $category->id,
            'name' => $category->name,
            'display' => $indent . $category->name,
            'root' => $rootName,
            'level' => $level,
            'type' => $category->type,
        ];

        // Add children
        $children = $childrenByParent->get($category->id, collect())->sortBy('sort_order');
        foreach ($children as $child) {
            static::addCategoryToList($result, $child, $childrenByParent, $level + 1);
        }
    }

    /**
     * Get all descendant IDs of a category.
     *
     * @param int $categoryId
     * @param int $maxDepth Maximum recursion depth (default 10)
     * @param int $currentDepth Current recursion depth (for internal use)
     * @param array $visited Array of visited IDs to prevent circular references
     * @return array
     */
    public static function getDescendantIds(int $categoryId, int $maxDepth = 10, int $currentDepth = 0, array $visited = []): array
    {
        // Prevent infinite recursion
        if ($currentDepth >= $maxDepth) {
            \Log::warning('Max recursion depth reached for getDescendantIds', [
                'category_id' => $categoryId,
                'max_depth' => $maxDepth
            ]);
            return [];
        }

        // Prevent circular references
        if (in_array($categoryId, $visited)) {
            \Log::warning('Circular reference detected in category hierarchy', [
                'category_id' => $categoryId,
                'visited' => $visited
            ]);
            return [];
        }

        $visited[] = $categoryId;
        $ids = [];
        $children = static::where('parent_id', $categoryId)->pluck('id');

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, static::getDescendantIds($childId, $maxDepth, $currentDepth + 1, $visited));
        }

        return $ids;
    }
}
