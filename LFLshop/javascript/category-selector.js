// Category Selection Interface for LFLshop Seller Dashboard
// Multi-level dropdown system with Ethiopian language support

class CategorySelector {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            showAmharic: options.showAmharic || false,
            allowCustom: options.allowCustom || false,
            required: options.required || true,
            placeholder: options.placeholder || 'Select a category...',
            onSelectionChange: options.onSelectionChange || null,
            ...options
        };
        
        this.selectedCategory = null;
        this.selectedSubcategory = null;
        this.selectedType = null;
        this.searchTimeout = null;
        
        this.init();
    }

    init() {
        this.render();
        this.setupEventListeners();
    }

    render() {
        const html = `
            <div class="category-selector">
                <div class="category-selector-header">
                    <h3>
                        <i class="fas fa-tags"></i>
                        Product Category
                        ${this.options.required ? '<span class="required">*</span>' : ''}
                    </h3>
                    <p class="category-help">Choose the most appropriate category for your product</p>
                </div>

                <!-- Search Bar -->
                <div class="category-search">
                    <div class="search-input-container">
                        <i class="fas fa-search search-icon"></i>
                        <input 
                            type="text" 
                            id="category-search-input" 
                            placeholder="Search categories... / ምድቦችን ይፈልጉ..."
                            class="category-search-input"
                        >
                        <button type="button" class="language-toggle" id="language-toggle">
                            <i class="fas fa-language"></i>
                            ${this.options.showAmharic ? 'አማ' : 'EN'}
                        </button>
                    </div>
                    <div class="search-results" id="search-results" style="display: none;"></div>
                </div>

                <!-- Category Selection Steps -->
                <div class="category-steps">
                    <!-- Step 1: Main Category -->
                    <div class="category-step" id="category-step">
                        <label class="step-label">
                            <span class="step-number">1</span>
                            Main Category
                        </label>
                        <div class="category-grid" id="category-grid">
                            ${this.renderCategoryGrid()}
                        </div>
                    </div>

                    <!-- Step 2: Subcategory -->
                    <div class="category-step" id="subcategory-step" style="display: none;">
                        <label class="step-label">
                            <span class="step-number">2</span>
                            Subcategory
                        </label>
                        <div class="subcategory-list" id="subcategory-list"></div>
                    </div>

                    <!-- Step 3: Product Type -->
                    <div class="category-step" id="type-step" style="display: none;">
                        <label class="step-label">
                            <span class="step-number">3</span>
                            Product Type
                        </label>
                        <div class="type-tags" id="type-tags"></div>
                    </div>
                </div>

                <!-- Selected Category Display -->
                <div class="selected-category" id="selected-category" style="display: none;">
                    <div class="selection-summary">
                        <h4>Selected Category:</h4>
                        <div class="breadcrumb" id="category-breadcrumb"></div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="this.clearSelection()">
                            <i class="fas fa-times"></i>
                            Change Category
                        </button>
                    </div>
                </div>

                <!-- Custom Category Option -->
                ${this.options.allowCustom ? this.renderCustomCategoryOption() : ''}

                <!-- Category Guidelines -->
                <div class="category-guidelines">
                    <details>
                        <summary>
                            <i class="fas fa-info-circle"></i>
                            Category Selection Guidelines
                        </summary>
                        <div class="guidelines-content">
                            <h4>Ethiopian Traditional Categories:</h4>
                            <ul>
                                <li><strong>Traditional Textiles:</strong> Authentic Ethiopian clothing and fabrics</li>
                                <li><strong>Jewelry & Accessories:</strong> Traditional crosses, silver work, and cultural jewelry</li>
                                <li><strong>Coffee & Beverages:</strong> Ethiopian coffee beans and traditional brewing equipment</li>
                                <li><strong>Pottery & Ceramics:</strong> Jebenas, traditional vessels, and ceramic art</li>
                                <li><strong>Spices & Food:</strong> Berbere, traditional spice blends, and Ethiopian foods</li>
                            </ul>
                            <h4>Selection Tips:</h4>
                            <ul>
                                <li>Choose the most specific category that fits your product</li>
                                <li>Traditional Ethiopian products should use cultural categories</li>
                                <li>Modern products can use standard e-commerce categories</li>
                                <li>Contact support if you're unsure about category placement</li>
                            </ul>
                        </div>
                    </details>
                </div>
            </div>
        `;

        this.container.innerHTML = html;
    }

    renderCategoryGrid() {
        const categories = CategoryManager.getAllCategories();
        const featuredCategories = CategoryManager.getFeaturedCategories();
        const otherCategories = Object.values(categories).filter(cat => !cat.featured);

        let html = '';

        // Featured Ethiopian Categories
        if (featuredCategories.length > 0) {
            html += '<div class="category-section"><h4 class="section-title">🇪🇹 Ethiopian Traditional Categories</h4><div class="category-cards">';
            featuredCategories.forEach(category => {
                html += this.renderCategoryCard(category, true);
            });
            html += '</div></div>';
        }

        // Other Categories
        if (otherCategories.length > 0) {
            html += '<div class="category-section"><h4 class="section-title">🛍️ General Categories</h4><div class="category-cards">';
            otherCategories.forEach(category => {
                html += this.renderCategoryCard(category, false);
            });
            html += '</div></div>';
        }

        return html;
    }

    renderCategoryCard(category, isFeatured) {
        const displayName = this.options.showAmharic ? category.nameAmharic : category.name;
        const description = this.options.showAmharic ? category.descriptionAmharic : category.description;
        
        return `
            <div class="category-card ${isFeatured ? 'featured' : ''}" 
                 data-category-id="${category.id}"
                 onclick="categorySelector.selectCategory('${category.id}')">
                <div class="category-icon">
                    <i class="${category.icon}"></i>
                    ${isFeatured ? '<span class="cultural-badge">🇪🇹</span>' : ''}
                </div>
                <div class="category-info">
                    <h4 class="category-name">${displayName}</h4>
                    <p class="category-description">${description}</p>
                    <span class="subcategory-count">
                        ${Object.keys(category.subcategories || {}).length} subcategories
                    </span>
                </div>
            </div>
        `;
    }

    renderCustomCategoryOption() {
        return `
            <div class="custom-category-section">
                <div class="custom-category-header">
                    <h4>
                        <i class="fas fa-plus-circle"></i>
                        Need a Custom Category?
                    </h4>
                    <p>Can't find the right category? Request a custom category for your products.</p>
                </div>
                <button type="button" class="btn btn-outline" onclick="this.showCustomCategoryForm()">
                    <i class="fas fa-plus"></i>
                    Request Custom Category
                </button>
            </div>
        `;
    }

    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('category-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim()) {
                    this.handleSearch(searchInput.value);
                }
            });

            // Hide search results when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.category-search')) {
                    this.hideSearchResults();
                }
            });
        }

        // Language toggle
        const languageToggle = document.getElementById('language-toggle');
        if (languageToggle) {
            languageToggle.addEventListener('click', () => {
                this.toggleLanguage();
            });
        }
    }

    handleSearch(query) {
        const searchResults = document.getElementById('search-results');
        if (!query.trim()) {
            this.hideSearchResults();
            return;
        }

        const results = CategoryManager.searchCategories(query);
        
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="no-results">No categories found</div>';
        } else {
            searchResults.innerHTML = results.map(result => {
                const displayName = this.options.showAmharic ? 
                    (result.subcategory?.nameAmharic || result.category.nameAmharic) :
                    (result.subcategory?.name || result.category.name);
                
                const breadcrumb = result.subcategory ? 
                    `${result.category.name} > ${result.subcategory.name}` :
                    result.category.name;

                return `
                    <div class="search-result-item" 
                         onclick="categorySelector.selectFromSearch('${result.category.id}', '${result.subcategory?.id || ''}')">
                        <div class="result-icon">
                            <i class="${result.category.icon}"></i>
                        </div>
                        <div class="result-info">
                            <div class="result-name">${displayName}</div>
                            <div class="result-breadcrumb">${breadcrumb}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        searchResults.style.display = 'block';
    }

    hideSearchResults() {
        const searchResults = document.getElementById('search-results');
        if (searchResults) {
            searchResults.style.display = 'none';
        }
    }

    selectCategory(categoryId) {
        this.selectedCategory = categoryId;
        this.selectedSubcategory = null;
        this.selectedType = null;

        const category = CategoryManager.getCategoryById(categoryId);
        if (!category) return;

        // Show subcategory step
        this.showSubcategories(category);
        
        // Update UI
        this.updateCategorySelection();
        this.hideSearchResults();
    }

    showSubcategories(category) {
        const subcategoryStep = document.getElementById('subcategory-step');
        const subcategoryList = document.getElementById('subcategory-list');

        if (!category.subcategories) {
            subcategoryStep.style.display = 'none';
            this.showSelectedCategory();
            return;
        }

        const subcategories = Object.values(category.subcategories);
        subcategoryList.innerHTML = subcategories.map(subcategory => {
            const displayName = this.options.showAmharic ? subcategory.nameAmharic : subcategory.name;
            
            return `
                <div class="subcategory-item" 
                     data-subcategory-id="${subcategory.id}"
                     onclick="categorySelector.selectSubcategory('${subcategory.id}')">
                    <div class="subcategory-info">
                        <h4 class="subcategory-name">${displayName}</h4>
                        <p class="subcategory-description">${subcategory.description}</p>
                        ${subcategory.types ? `<span class="type-count">${subcategory.types.length} product types</span>` : ''}
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </div>
            `;
        }).join('');

        subcategoryStep.style.display = 'block';
    }

    selectSubcategory(subcategoryId) {
        this.selectedSubcategory = subcategoryId;
        this.selectedType = null;

        const subcategory = CategoryManager.getSubcategoryById(this.selectedCategory, subcategoryId);
        if (!subcategory) return;

        // Show product types if available
        if (subcategory.types && subcategory.types.length > 0) {
            this.showProductTypes(subcategory);
        } else {
            this.showSelectedCategory();
        }

        this.updateCategorySelection();
    }

    showProductTypes(subcategory) {
        const typeStep = document.getElementById('type-step');
        const typeTags = document.getElementById('type-tags');

        typeTags.innerHTML = subcategory.types.map(type => `
            <button type="button" class="type-tag" 
                    data-type="${type}"
                    onclick="categorySelector.selectType('${type}')">
                ${type}
            </button>
        `).join('');

        typeStep.style.display = 'block';
    }

    selectType(type) {
        this.selectedType = type;
        this.updateCategorySelection();
        this.showSelectedCategory();
    }

    selectFromSearch(categoryId, subcategoryId = '') {
        this.selectCategory(categoryId);
        if (subcategoryId) {
            setTimeout(() => {
                this.selectSubcategory(subcategoryId);
            }, 100);
        }
    }

    updateCategorySelection() {
        // Update visual selection states
        document.querySelectorAll('.category-card').forEach(card => {
            card.classList.toggle('selected', card.dataset.categoryId === this.selectedCategory);
        });

        document.querySelectorAll('.subcategory-item').forEach(item => {
            item.classList.toggle('selected', item.dataset.subcategoryId === this.selectedSubcategory);
        });

        document.querySelectorAll('.type-tag').forEach(tag => {
            tag.classList.toggle('selected', tag.dataset.type === this.selectedType);
        });

        // Trigger callback
        if (this.options.onSelectionChange) {
            this.options.onSelectionChange({
                category: this.selectedCategory,
                subcategory: this.selectedSubcategory,
                type: this.selectedType
            });
        }
    }

    showSelectedCategory() {
        const selectedDiv = document.getElementById('selected-category');
        const breadcrumbDiv = document.getElementById('category-breadcrumb');

        if (!this.selectedCategory) {
            selectedDiv.style.display = 'none';
            return;
        }

        const breadcrumb = CategoryManager.getCategoryBreadcrumb(this.selectedCategory, this.selectedSubcategory);
        let breadcrumbText = breadcrumb.map(item => 
            this.options.showAmharic ? (item.nameAmharic || item.name) : item.name
        ).join(' > ');

        if (this.selectedType) {
            breadcrumbText += ` > ${this.selectedType}`;
        }

        breadcrumbDiv.textContent = breadcrumbText;
        selectedDiv.style.display = 'block';

        // Hide steps
        document.getElementById('subcategory-step').style.display = 'none';
        document.getElementById('type-step').style.display = 'none';
    }

    toggleLanguage() {
        this.options.showAmharic = !this.options.showAmharic;
        const toggle = document.getElementById('language-toggle');
        toggle.innerHTML = `<i class="fas fa-language"></i> ${this.options.showAmharic ? 'አማ' : 'EN'}`;
        
        // Re-render category grid
        const categoryGrid = document.getElementById('category-grid');
        categoryGrid.innerHTML = this.renderCategoryGrid();
        
        // Update any visible subcategories
        if (this.selectedCategory) {
            const category = CategoryManager.getCategoryById(this.selectedCategory);
            this.showSubcategories(category);
        }
    }

    clearSelection() {
        this.selectedCategory = null;
        this.selectedSubcategory = null;
        this.selectedType = null;
        
        document.getElementById('selected-category').style.display = 'none';
        document.getElementById('subcategory-step').style.display = 'none';
        document.getElementById('type-step').style.display = 'none';
        
        this.updateCategorySelection();
    }

    getSelection() {
        return {
            category: this.selectedCategory,
            subcategory: this.selectedSubcategory,
            type: this.selectedType,
            isValid: this.selectedCategory !== null
        };
    }

    setSelection(categoryId, subcategoryId = null, type = null) {
        this.selectedCategory = categoryId;
        this.selectedSubcategory = subcategoryId;
        this.selectedType = type;
        
        if (categoryId) {
            const category = CategoryManager.getCategoryById(categoryId);
            if (category && subcategoryId) {
                this.showSubcategories(category);
                const subcategory = CategoryManager.getSubcategoryById(categoryId, subcategoryId);
                if (subcategory && type && subcategory.types) {
                    this.showProductTypes(subcategory);
                }
            }
        }
        
        this.updateCategorySelection();
        this.showSelectedCategory();
    }
}

// Global instance for easy access
let categorySelector = null;

// Initialize category selector
function initializeCategorySelector(containerId, options = {}) {
    categorySelector = new CategorySelector(containerId, options);
    return categorySelector;
}

// Export for global use
window.CategorySelector = CategorySelector;
window.initializeCategorySelector = initializeCategorySelector;
