# Reusable Components Documentation

## Overview

This document describes the reusable components created for the CCTV Dashboard application, following DRY (Don't Repeat Yourself) principles.

## Components

### 1. Form Components

#### `x-input`

Reusable input component with built-in validation, icons, and error handling.

**Props:**

- `label` - Input label
- `name` - Input name attribute
- `type` - Input type (text, email, password, etc.)
- `value` - Input value
- `placeholder` - Placeholder text
- `required` - Boolean for required field
- `disabled` - Boolean for disabled state
- `error` - Error message
- `hint` - Helper text
- `icon` - SVG icon path

**Usage:**

```blade
<x-input
  label="Email Address"
  name="email"
  type="email"
  :value="old('email')"
  placeholder="Enter your email"
  required
  icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207' />"
/>
```

#### `x-checkbox`

Reusable checkbox component with label and error handling.

**Props:**

- `label` - Checkbox label
- `name` - Checkbox name attribute
- `value` - Checkbox value
- `checked` - Boolean for checked state
- `disabled` - Boolean for disabled state
- `error` - Error message

**Usage:**

```blade
<x-checkbox
  name="remember"
  label="Remember me for 30 days"
/>
```

#### `x-form-wrapper`

Reusable form wrapper with automatic CSRF handling and method spoofing.

**Props:**

- `title` - Form title
- `subtitle` - Form subtitle
- `method` - HTTP method (POST, PUT, PATCH, DELETE)
- `action` - Form action URL
- `class` - Additional CSS classes
- `showCsrf` - Boolean to show/hide CSRF token
- `enctype` - Form encoding type

**Usage:**

```blade
<x-form-wrapper
  method="POST"
  action="{{ route('login') }}"
  class="space-y-6">
  <!-- Form content -->
</x-form-wrapper>
```

### 2. Button Components

#### `x-button`

Reusable button component with multiple variants and sizes.

**Props:**

- `variant` - Button style (primary, secondary, success, danger, warning, purple, outline)
- `size` - Button size (sm, md, lg)
- `type` - Button type (button, submit, reset)
- `href` - Link URL (creates anchor tag)
- `icon` - SVG icon path

**Usage:**

```blade
<x-button
  type="submit"
  variant="primary"
  size="lg"
  class="w-full shadow-lg">
  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
  </svg>
  Sign In
</x-button>
```

#### `x-loading-button`

Button component with loading state and spinner.

**Props:**

- `variant` - Button style
- `size` - Button size
- `type` - Button type
- `loading` - Boolean for loading state
- `loadingText` - Text to show when loading
- `icon` - SVG icon path
- `href` - Link URL

**Usage:**

```blade
<x-loading-button
  type="submit"
  variant="primary"
  :loading="false"
  loading-text="Saving..."
  class="w-full">
  Save Changes
</x-loading-button>
```

### 3. Layout Components

#### `x-card`

Reusable card component with optional title and padding.

**Props:**

- `title` - Card title
- `padding` - Boolean for internal padding

**Usage:**

```blade
<x-card class="backdrop-blur-sm bg-white/95 shadow-2xl border border-white/20">
  <!-- Card content -->
</x-card>
```

### 4. Utility Components

#### `x-spinner`

Loading spinner component with different sizes and colors.

**Props:**

- `size` - Spinner size (sm, md, lg, xl)
- `color` - Spinner color (blue, gray, white, green, red)

**Usage:**

```blade
<x-spinner size="md" color="blue" />
```

#### `x-notification`

Reusable notification component with different types and positions.

**Props:**

- `type` - Notification type (success, error, warning, info)
- `message` - Notification message
- `duration` - Auto-close duration in milliseconds
- `position` - Notification position (top-right, top-left, bottom-right, bottom-left, top-center, bottom-center)

**Usage:**

```blade
<x-notification
  type="success"
  message="Demo credentials filled!"
  duration="3000"
  position="top-right"
/>
```

## JavaScript Functions

### `showNotification(message, type, duration, position)`

Global function to show notifications programmatically.

**Parameters:**

- `message` - Notification message
- `type` - Notification type (success, error, warning, info)
- `duration` - Auto-close duration (default: 3000ms)
- `position` - Notification position (default: top-right)

**Usage:**

```javascript
showNotification("Demo credentials filled!", "success");
showNotification("Error occurred!", "error", 5000, "top-center");
```

## Design Principles

### 1. DRY (Don't Repeat Yourself)

- All components are reusable across the application
- Common functionality is centralized in components
- No duplicate code for similar UI elements

### 2. Consistency

- All components follow the same design system
- Consistent spacing, colors, and typography
- Unified error handling and validation

### 3. Accessibility

- Proper ARIA labels and attributes
- Keyboard navigation support
- Screen reader friendly

### 4. Responsiveness

- Mobile-first design approach
- Responsive breakpoints
- Flexible layouts

## Usage Guidelines

1. **Always use components** instead of writing custom HTML
2. **Pass props explicitly** for better maintainability
3. **Use semantic naming** for component props
4. **Follow the established patterns** for new components
5. **Test components** in different contexts

## Future Enhancements

- [ ] Add more form components (select, textarea, file upload)
- [ ] Create modal components
- [ ] Add data table components
- [ ] Implement chart components
- [ ] Add animation components
