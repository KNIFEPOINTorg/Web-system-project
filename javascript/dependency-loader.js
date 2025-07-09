/**
 * JavaScript Dependency Loader for LFLshop
 * Ensures proper loading order and handles missing dependencies
 */

class DependencyLoader {
    constructor() {
        this.loadedScripts = new Set();
        this.loadingPromises = new Map();
        this.dependencies = {
            'config.js': [],
            'auth.js': ['config.js'],
            'cart.js': ['config.js'],
            'collections.js': ['config.js'],
            'auth-aware-navigation.js': ['config.js', 'auth.js'],
            'search-functionality.js': ['config.js'],
            'enhanced-search.js': ['config.js'],
            'auth-state-manager.js': ['config.js', 'auth.js']
        };
    }

    /**
     * Load a script with its dependencies
     */
    async loadScript(scriptName, basePath = '../javascript/') {
        // If already loaded, return immediately
        if (this.loadedScripts.has(scriptName)) {
            return Promise.resolve();
        }

        // If currently loading, return the existing promise
        if (this.loadingPromises.has(scriptName)) {
            return this.loadingPromises.get(scriptName);
        }

        // Create loading promise
        const loadingPromise = this._loadScriptWithDependencies(scriptName, basePath);
        this.loadingPromises.set(scriptName, loadingPromise);

        try {
            await loadingPromise;
            this.loadedScripts.add(scriptName);
            this.loadingPromises.delete(scriptName);
        } catch (error) {
            this.loadingPromises.delete(scriptName);
            throw error;
        }

        return loadingPromise;
    }

    /**
     * Load script with its dependencies
     */
    async _loadScriptWithDependencies(scriptName, basePath) {
        const deps = this.dependencies[scriptName] || [];
        
        // Load all dependencies first
        await Promise.all(deps.map(dep => this.loadScript(dep, basePath)));
        
        // Then load the script itself
        return this._loadSingleScript(scriptName, basePath);
    }

    /**
     * Load a single script file
     */
    _loadSingleScript(scriptName, basePath) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = basePath + scriptName;
            script.async = false; // Maintain execution order
            
            script.onload = () => {
                console.log(`✅ Loaded: ${scriptName}`);
                resolve();
            };
            
            script.onerror = () => {
                console.error(`❌ Failed to load: ${scriptName}`);
                reject(new Error(`Failed to load script: ${scriptName}`));
            };
            
            document.head.appendChild(script);
        });
    }

    /**
     * Load multiple scripts in dependency order
     */
    async loadScripts(scriptNames, basePath = '../javascript/') {
        try {
            await Promise.all(scriptNames.map(name => this.loadScript(name, basePath)));
            console.log('✅ All scripts loaded successfully');
        } catch (error) {
            console.error('❌ Script loading failed:', error);
            throw error;
        }
    }

    /**
     * Check if required globals are available
     */
    checkGlobals() {
        const requiredGlobals = ['LFLConfig', 'ApiHelper', 'CurrencyHelper'];
        const missing = requiredGlobals.filter(global => typeof window[global] === 'undefined');
        
        if (missing.length > 0) {
            console.warn('⚠️ Missing global dependencies:', missing);
            return false;
        }
        
        return true;
    }

    /**
     * Wait for a global variable to be available
     */
    waitForGlobal(globalName, timeout = 5000) {
        return new Promise((resolve, reject) => {
            if (typeof window[globalName] !== 'undefined') {
                resolve(window[globalName]);
                return;
            }

            const startTime = Date.now();
            const checkInterval = setInterval(() => {
                if (typeof window[globalName] !== 'undefined') {
                    clearInterval(checkInterval);
                    resolve(window[globalName]);
                } else if (Date.now() - startTime > timeout) {
                    clearInterval(checkInterval);
                    reject(new Error(`Timeout waiting for global: ${globalName}`));
                }
            }, 100);
        });
    }

    /**
     * Initialize with error handling
     */
    async initialize(requiredScripts = ['config.js']) {
        try {
            await this.loadScripts(requiredScripts);
            
            // Wait for essential globals
            await this.waitForGlobal('LFLConfig');
            
            console.log('✅ Dependency loader initialized successfully');
            return true;
        } catch (error) {
            console.error('❌ Dependency loader initialization failed:', error);
            
            // Show user-friendly error
            this.showLoadingError(error);
            return false;
        }
    }

    /**
     * Show loading error to user
     */
    showLoadingError(error) {
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4444;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 10000;
            max-width: 300px;
            font-family: Arial, sans-serif;
        `;
        
        errorDiv.innerHTML = `
            <strong>Loading Error</strong><br>
            Some features may not work properly.<br>
            <small>${error.message}</small><br>
            <button onclick="location.reload()" style="margin-top: 10px; padding: 5px 10px; background: white; color: #ff4444; border: none; border-radius: 3px; cursor: pointer;">
                Reload Page
            </button>
        `;
        
        document.body.appendChild(errorDiv);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 10000);
    }
}

// Create global instance
window.DependencyLoader = new DependencyLoader();

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.DependencyLoader.initialize();
    });
} else {
    window.DependencyLoader.initialize();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DependencyLoader;
}
