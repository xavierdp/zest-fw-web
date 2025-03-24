# ZestPHP Web Framework

A web extension for ZestPHP Core that adds Twig templating, jQuery, and TailwindCSS integration.

## Overview

ZestPHP Web extends the ZestPHP Core framework with modern web development capabilities while maintaining the simplicity and efficiency of the core framework. It provides a component-based architecture for building reusable UI elements and a powerful templating system based on Twig.

## Features

- **Twig Templating**: Powerful and flexible template engine
- **Component System**: Create reusable UI components with PHP, Twig, CSS, and JavaScript
- **TailwindCSS Integration**: Modern utility-first CSS framework
- **jQuery Support**: Easy DOM manipulation and AJAX functionality
- **Asset Management**: Organized structure for static assets
- **Routing**: Uses ZestPHP Core's routing system
- **API Support**: Uses ZestPHP Core's API engine

## Directory Structure

```
zest-fw-web/                # Framework files
├── boilerplate/            # Boilerplate files for new applications
│   ├── lib/                # Composer configuration
│   ├── templates/          # Twig templates
│   └── webroot/            # Web root directory
│       ├── index.php       # Application entry point
│       └── static/         # Static assets
│           ├── components/ # UI components
│           ├── css/        # CSS files
│           ├── js/         # JavaScript files
│           ├── img/        # Images
│           └── fonts/      # Fonts
└── zest-fw-web.php         # Main framework file

app/                        # Application directory
├── lib/                    # Composer dependencies
├── templates/              # Twig templates
└── webroot/                # Web root directory
    ├── index.php           # Application entry point
    └── static/             # Public static assets
        ├── components/     # UI components
        ├── css/            # CSS files
        ├── js/             # JavaScript files
        ├── img/            # Images
        └── fonts/          # Fonts
```

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/zest-fw-web.git
   ```

2. Initialize a new application:
   ```
   php zest-fw-web.php --init
   ```

3. Install dependencies:
   ```
   cd app/lib
   composer install
   ```

## Component System

ZestPHP Web includes a powerful component system for creating reusable UI elements. Each component consists of four files:

- `ComponentName.php`: PHP class for component logic
- `ComponentName.twig`: Twig template for component rendering
- `ComponentName.css`: CSS styles for the component
- `ComponentName.js`: JavaScript functionality for the component

### Creating a Component

Components are stored in the `app/webroot/static/components/` directory, with each component in its own subdirectory:

```
app/webroot/static/components/Button/
├── Button.php
├── Button.twig
├── Button.css
└── Button.js
```

You can create a new component using the `create_component()` function:

```php
create_component('MyComponent');
```

### Using Components in Templates

Components can be used in Twig templates with the `component()` function:

```twig
{{ component('Button', {text: 'Click Me', type: 'primary'}) }}
```

## Template System

ZestPHP Web uses Twig as its template engine. Templates are stored in the `app/templates/` directory.

### Basic Template Structure

```twig
{% extends 'base.twig' %}

{% block title %}Page Title{% endblock %}

{% block content %}
    <h1>Hello, World!</h1>
    
    {# Using a component #}
    {{ component('Button', {text: 'Click Me'}) }}
{% endblock %}
```

### Rendering Templates

Templates can be rendered using the `display_template()` function:

```php
display_template('home.twig', [
    'title' => 'Welcome',
    'content' => 'This is the homepage'
]);
```

## Routing

ZestPHP Web uses the routing system from ZestPHP Core:

```php
// Example route with template rendering
if ($a = route("^/$")) {
    display_template('home.twig', [
        'title' => 'Welcome',
        'content' => 'This is the homepage'
    ]);
    exit;
}

// Example API route
if ($a = route("^/api/hello$")) {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'Hello from ZestPHP Web API',
        'timestamp' => time()
    ]);
    exit;
}
```

## Dependencies

- PHP 7.4 or higher
- Twig 3.0 or higher
- jQuery 3.6.0 or higher
- TailwindCSS 3.0 or higher

## License

[MIT License](LICENSE)
