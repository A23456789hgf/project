# Project Tables Unified CSS System Guide

## Overview
This guide explains the unified CSS system for all project tables to ensure consistency and avoid conflicts across the project management application.

## Files Structure
```
resources/
├── css/
│   ├── project-tables.css          # Main unified CSS file
│   └── PROJECT_TABLES_GUIDE.md     # This guide
└── views/
    └── projects/
        └── partials/
            └── tables/
                ├── _unified_table_template.blade.php  # Reusable template
                ├── specific_objectives.blade.php      # Updated to use unified CSS
                ├── project_location.blade.php         # Updated to use unified CSS
                ├── project_obstacles.blade.php        # Updated to use unified CSS
                ├── financing_unified.blade.php        # New unified financing table
                └── ...
```

## CSS Classes Reference

### Container Classes
- `.project-table-container` - Main wrapper for the entire table component
- `.project-table-header` - Header section with title and icon
- `.project-table-wrapper` - Wrapper for the table and controls
- `.project-table` - The actual table element

### Button Classes
- `.project-btn` - Base button class
- `.project-btn-primary` - Primary action buttons (green gradient)
- `.project-btn-success` - Success buttons (green)
- `.project-btn-danger` - Delete/remove buttons (red)
- `.project-btn-warning` - Warning buttons (yellow)
- `.project-btn-info` - Info buttons (blue)

### Layout Classes
- `.project-action-buttons` - Container for action buttons in table cells
- `.project-empty-row` - Empty state row styling
- `.project-animated-row` - Row with slide-in animation
- `.project-result-container` - Special container for objective results
- `.project-output-item` - Container for result outputs

### Utility Classes
- `.project-text-primary` - Primary color text
- `.project-text-accent` - Accent color text
- `.project-bg-primary` - Primary background color
- `.project-bg-light` - Light background color
- `.project-border-primary` - Primary border color

## Color Scheme
```css
:root {
    --project-primary: #2c5f2d;      /* Main green */
    --project-accent: #97bc62;       /* Light green */
    --project-hover: #204722;        /* Dark green */
    --project-light: #e8f5e9;        /* Very light green */
    --project-success: #28a745;      /* Bootstrap success */
    --project-danger: #dc3545;       /* Bootstrap danger */
    --project-warning: #ffc107;      /* Bootstrap warning */
    --project-info: #17a2b8;         /* Bootstrap info */
}
```

## Usage Examples

### Basic Table Structure
```html
<div class="project-table-container">
    <div class="project-table-header">
        <i class="fas fa-table"></i>Table Title
    </div>
    
    <div class="project-table-wrapper">
        <table class="project-table" id="myTable">
            <thead>
                <tr>
                    <th>Column 1</th>
                    <th>Column 2</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Table rows here -->
            </tbody>
        </table>
        
        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addBtn">
                <i class="fas fa-plus"></i>Add New
            </button>
        </div>
    </div>
</div>
```

### Using the Unified Template
```php
@include('projects.partials.tables._unified_table_template', [
    'tableId' => 'myTable',
    'title' => 'My Table',
    'icon' => 'fas fa-table',
    'headers' => ['Column 1', 'Column 2', 'Actions'],
    'emptyMessage' => 'No data available',
    'addButtonText' => 'Add New Item',
    'addButtonId' => 'addBtn',
    'showAddButton' => true,
    'hasData' => false
])
```

### Action Buttons in Table Cells
```html
<td data-label="Actions">
    <div class="project-action-buttons">
        <button type="button" class="project-btn project-btn-success">
            <i class="fas fa-edit"></i>Edit
        </button>
        <button type="button" class="project-btn project-btn-danger">
            <i class="fas fa-trash"></i>Delete
        </button>
    </div>
</td>
```

### Empty State Row
```html
<tr class="project-empty-row">
    <td colspan="3" class="text-center">
        <i class="fas fa-inbox fa-2x mb-2"></i><br>
        No items found
    </td>
</tr>
```

## JavaScript Integration

### Basic Table Manager Pattern
```javascript
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const TableManager = {
            counter: 0,
            
            init: function() {
                this.bindEvents();
                console.log('✅ Table Manager Initialized');
            },
            
            bindEvents: function() {
                // Add button
                const addBtn = document.getElementById('addBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => this.addRow());
                }
                
                // Remove button (delegated)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-row')) {
                        this.removeRow(e.target.closest('tr'));
                    }
                });
            },
            
            addRow: function() {
                // Remove empty row if exists
                const emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) {
                    emptyRow.remove();
                }
                
                const tbody = document.querySelector('#myTable tbody');
                const row = this.createRow();
                tbody.appendChild(row);
                
                // Add animation
                row.classList.add('project-animated-row');
                this.counter++;
            },
            
            createRow: function() {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td data-label="Column 1">
                        <input type="text" name="items[${this.counter}][field1]" 
                               class="form-control" required>
                    </td>
                    <td data-label="Column 2">
                        <input type="text" name="items[${this.counter}][field2]" 
                               class="form-control" required>
                    </td>
                    <td data-label="Actions">
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-danger remove-row">
                                <i class="fas fa-trash"></i>Delete
                            </button>
                        </div>
                    </td>
                `;
                return row;
            },
            
            removeRow: function(row) {
                if (confirm('Are you sure you want to delete this item?')) {
                    row.remove();
                    this.showEmptyRowIfNeeded();
                }
            },
            
            showEmptyRowIfNeeded: function() {
                const tbody = document.querySelector('#myTable tbody');
                if (tbody.children.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'project-empty-row';
                    emptyRow.innerHTML = `
                        <td colspan="3" class="text-center">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            No items found
                        </td>
                    `;
                    tbody.appendChild(emptyRow);
                }
            }
        };
        
        TableManager.init();
    });
})();
```

## Responsive Design

The unified CSS includes responsive breakpoints:

- **Desktop (>768px)**: Full table layout
- **Tablet (≤768px)**: Stacked card layout with labels
- **Mobile (≤576px)**: Optimized spacing and button sizes

### Mobile-Friendly Data Labels
Add `data-label` attributes to table cells for mobile display:

```html
<td data-label="Column Name">Cell Content</td>
```

## Migration Guide

### Converting Existing Tables

1. **Replace container structure**:
   ```html
   <!-- Old -->
   <div class="card">
       <div class="card-header">Title</div>
       <div class="card-body">
           <table class="table">
   
   <!-- New -->
   <div class="project-table-container">
       <div class="project-table-header">
           <i class="fas fa-icon"></i>Title
       </div>
       <div class="project-table-wrapper">
           <table class="project-table">
   ```

2. **Update button classes**:
   ```html
   <!-- Old -->
   <button class="btn btn-primary">Add</button>
   <button class="btn btn-danger">Delete</button>
   
   <!-- New -->
   <button class="project-btn project-btn-primary">Add</button>
   <button class="project-btn project-btn-danger">Delete</button>
   ```

3. **Update empty state**:
   ```html
   <!-- Old -->
   <tr class="empty-row">
   
   <!-- New -->
   <tr class="project-empty-row">
   ```

4. **Update JavaScript selectors**:
   ```javascript
   // Old
   document.querySelector('.empty-row')
   
   // New
   document.querySelector('.project-empty-row')
   ```

## Best Practices

1. **Consistent Icons**: Use Font Awesome icons consistently across tables
2. **Semantic Colors**: Use appropriate button colors (danger for delete, success for add, etc.)
3. **Accessibility**: Include proper labels and ARIA attributes
4. **Performance**: Use event delegation for dynamic content
5. **Validation**: Include proper form validation for input fields

## Troubleshooting

### Common Issues

1. **Styles not applying**: Ensure `project-tables.css` is loaded in the layout
2. **JavaScript errors**: Check console for missing elements or selectors
3. **Mobile layout issues**: Verify `data-label` attributes are present
4. **Animation not working**: Ensure `project-animated-row` class is added after DOM insertion

### Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Future Enhancements

- [ ] Dark mode support
- [ ] Additional color themes
- [ ] Enhanced animations
- [ ] Accessibility improvements
- [ ] Print-friendly styles

## Contributing

When adding new table components:

1. Use the unified CSS classes
2. Follow the established naming conventions
3. Include proper responsive design
4. Add JavaScript using the manager pattern
5. Update this documentation

---

For questions or issues, please refer to the project documentation or contact the development team.