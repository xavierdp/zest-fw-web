// ZestPHP Web Framework - Main JS

document.addEventListener('DOMContentLoaded', function() {
  console.log('ZestPHP Web Framework initialized');
  
  // Initialize any components that need JavaScript
  initializeComponents();
});

function initializeComponents() {
  // This function will initialize all components on the page
  const components = document.querySelectorAll('[data-component]');
  
  components.forEach(component => {
    const componentName = component.dataset.component;
    console.log(`Initializing component: ${componentName}`);
    
    // Component-specific initialization can be added here
    switch(componentName) {
      case 'Button':
        // Button component initialization
        break;
      // Add other components as needed
    }
  });
}
