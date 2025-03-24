<?php
/**
 * Button Component
 * 
 * A reusable button component with different styles and sizes
 */
class ButtonComponent {
    /**
     * Prepare data for the component
     * 
     * @param array $data Input data
     * @return array Prepared data
     */
    public function prepare($data = []) {
        // Default values
        $defaults = [
            'text' => 'Button',
            'type' => 'primary', // primary, secondary, success, danger, warning, info
            'size' => 'md',      // sm, md, lg
            'url' => null,       // If set, button will be a link
            'disabled' => false,
            'onClick' => null,   // JavaScript onClick handler
            'attributes' => []   // Additional HTML attributes
        ];
        
        // Merge defaults with provided data
        $data = array_merge($defaults, $data);
        
        // Generate CSS classes based on type and size
        $classes = ['zest-button'];
        
        // Type classes
        switch ($data['type']) {
            case 'primary':
                $classes[] = 'bg-blue-600 hover:bg-blue-700 text-white';
                break;
            case 'secondary':
                $classes[] = 'bg-gray-500 hover:bg-gray-600 text-white';
                break;
            case 'success':
                $classes[] = 'bg-green-600 hover:bg-green-700 text-white';
                break;
            case 'danger':
                $classes[] = 'bg-red-600 hover:bg-red-700 text-white';
                break;
            case 'warning':
                $classes[] = 'bg-yellow-500 hover:bg-yellow-600 text-white';
                break;
            case 'info':
                $classes[] = 'bg-blue-400 hover:bg-blue-500 text-white';
                break;
            default:
                $classes[] = 'bg-blue-600 hover:bg-blue-700 text-white';
        }
        
        // Size classes
        switch ($data['size']) {
            case 'sm':
                $classes[] = 'text-sm px-2 py-1';
                break;
            case 'md':
                $classes[] = 'text-base px-4 py-2';
                break;
            case 'lg':
                $classes[] = 'text-lg px-6 py-3';
                break;
            default:
                $classes[] = 'text-base px-4 py-2';
        }
        
        // Disabled state
        if ($data['disabled']) {
            $classes[] = 'opacity-50 cursor-not-allowed';
        }
        
        // Add classes to data
        $data['classes'] = implode(' ', $classes);
        
        // Generate attributes string
        $attributesArray = $data['attributes'];
        if ($data['onClick'] && !$data['disabled']) {
            $attributesArray['onclick'] = $data['onClick'];
        }
        
        $attributesStr = '';
        foreach ($attributesArray as $key => $value) {
            $attributesStr .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $data['attributesStr'] = $attributesStr;
        
        return $data;
    }
}
