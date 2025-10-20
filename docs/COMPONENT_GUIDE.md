# Component Guide - CCTV Dashboard

## 🧩 Overview

This guide covers all reusable Blade components in the CCTV Dashboard. Components follow a consistent design system and are built with Tailwind CSS and Alpine.js for interactivity.

---

## 📋 Table of Contents

1. [Form Components](#form-components)
2. [UI Components](#ui-components)
3. [Layout Components](#layout-components)
4. [Interactive Components](#interactive-components)
5. [Component Usage Examples](#component-usage-examples)

---

## 📝 Form Components

### Input Component

**File:** `resources/views/components/input.blade.php`

A versatile input component with support for icons, validation, and hints.

#### Props

- `label` (string, optional): Input label
- `name` (string, required): Input name attribute
- `type` (string, default: 'text'): Input type
- `value` (string, default: ''): Input value
- `placeholder` (string, default: ''): Placeholder text
- `required` (boolean, default: false): Required field
- `disabled` (boolean, default: false): Disabled state
- `error` (string, optional): Error message
- `hint` (string, optional): Help text
- `icon` (string, optional): SVG icon path

#### Usage

```blade
{{-- Basic input --}}
<x-input name="email" label="Email Address" type="email" required />

{{-- Input with icon --}}
<x-input
  name="search"
  label="Search"
  placeholder="Search users..."
  :icon="'<path d=\"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\"/>'"
/>

{{-- Input with hint --}}
<x-input
  name="password"
  label="Password"
  type="password"
  hint="Must be at least 6 characters"
  required
/>

{{-- Input with error --}}
<x-input
  name="email"
  label="Email"
  :error="$errors->first('email')"
/>
```

### Textarea Component

**File:** `resources/views/components/textarea.blade.php`

Multi-line text input component.

#### Props

- `label` (string, optional): Textarea label
- `name` (string, required): Textarea name attribute
- `value` (string, default: ''): Textarea value
- `placeholder` (string, default: ''): Placeholder text
- `required` (boolean, default: false): Required field
- `disabled` (boolean, default: false): Disabled state
- `rows` (integer, default: 4): Number of rows
- `error` (string, optional): Error message
- `hint` (string, optional): Help text

#### Usage

```blade
{{-- Basic textarea --}}
<x-textarea name="description" label="Description" rows="5" />

{{-- Textarea with hint --}}
<x-textarea
  name="bio"
  label="Biography"
  hint="Tell us about yourself"
  rows="6"
/>

{{-- Textarea with error --}}
<x-textarea
  name="content"
  label="Content"
  :error="$errors->first('content')"
/>
```

### Select Component

**File:** `resources/views/components/select.blade.php`

Dropdown select component with options support.

#### Props

- `label` (string, optional): Select label
- `name` (string, required): Select name attribute
- `options` (array, default: []): Options array (value => label)
- `selected` (string, default: ''): Selected value
- `placeholder` (string, default: 'Select an option'): Placeholder text
- `required` (boolean, default: false): Required field
- `disabled` (boolean, default: false): Disabled state
- `error` (string, optional): Error message
- `hint` (string, optional): Help text

#### Usage

```blade
{{-- Basic select --}}
<x-select
  name="role"
  label="User Role"
  :options="['user' => 'User', 'admin' => 'Administrator']"
  required
/>

{{-- Select with placeholder --}}
<x-select
  name="country"
  label="Country"
  :options="$countries"
  placeholder="Choose your country"
/>

{{-- Select with selected value --}}
<x-select
  name="status"
  label="Status"
  :options="['active' => 'Active', 'inactive' => 'Inactive']"
  :selected="$user->status"
/>
```

### Checkbox Component

**File:** `resources/views/components/checkbox.blade.php`

Checkbox input component.

#### Props

- `label` (string, optional): Checkbox label
- `name` (string, required): Checkbox name attribute
- `value` (string, default: '1'): Checkbox value
- `checked` (boolean, default: false): Checked state
- `disabled` (boolean, default: false): Disabled state
- `error` (string, optional): Error message

#### Usage

```blade
{{-- Basic checkbox --}}
<x-checkbox name="agree" label="I agree to the terms" />

{{-- Checked checkbox --}}
<x-checkbox name="newsletter" label="Subscribe to newsletter" checked />

{{-- Checkbox with custom value --}}
<x-checkbox name="permissions[]" value="read" label="Read Permission" />
```

### Radio Component

**File:** `resources/views/components/radio.blade.php`

Radio button component.

#### Props

- `label` (string, required): Radio label
- `name` (string, required): Radio name attribute
- `value` (string, required): Radio value
- `checked` (boolean, default: false): Checked state
- `disabled` (boolean, default: false): Disabled state

#### Usage

```blade
{{-- Radio buttons --}}
<x-radio name="gender" value="male" label="Male" />
<x-radio name="gender" value="female" label="Female" />
<x-radio name="gender" value="other" label="Other" />

{{-- Radio with checked state --}}
<x-radio name="plan" value="premium" label="Premium Plan" checked />
```

### Toggle Component

**File:** `resources/views/components/toggle.blade.php`

Modern toggle switch component using Alpine.js.

#### Props

- `label` (string, optional): Toggle label
- `name` (string, required): Toggle name attribute
- `checked` (boolean, default: false): Checked state
- `disabled` (boolean, default: false): Disabled state
- `size` (string, default: 'md'): Size (sm, md, lg)

#### Usage

```blade
{{-- Basic toggle --}}
<x-toggle name="notifications" label="Enable Notifications" />

{{-- Toggle with different sizes --}}
<x-toggle name="dark_mode" label="Dark Mode" size="lg" />

{{-- Checked toggle --}}
<x-toggle name="auto_save" label="Auto Save" checked />
```

### File Upload Component

**File:** `resources/views/components/file-upload.blade.php`

File upload component with drag & drop support.

#### Props

- `label` (string, optional): Upload label
- `name` (string, required): Input name attribute
- `accept` (string, optional): Accepted file types
- `required` (boolean, default: false): Required field
- `multiple` (boolean, default: false): Multiple files
- `error` (string, optional): Error message
- `hint` (string, optional): Help text

#### Usage

```blade
{{-- Basic file upload --}}
<x-file-upload name="avatar" label="Profile Photo" />

{{-- Image upload only --}}
<x-file-upload
  name="image"
  label="Upload Image"
  accept="image/*"
  hint="PNG, JPG up to 2MB"
/>

{{-- Multiple files --}}
<x-file-upload
  name="documents"
  label="Upload Documents"
  multiple
  hint="Select multiple files"
/>
```

---

## 🎨 UI Components

### Button Component

**File:** `resources/views/components/button.blade.php`

Versatile button component with multiple variants and sizes.

#### Props

- `variant` (string, default: 'primary'): Button style (primary, secondary, success, danger, warning, outline)
- `size` (string, default: 'md'): Button size (sm, md, lg)
- `type` (string, default: 'button'): Button type
- `href` (string, optional): Link URL (renders as anchor)
- `icon` (string, optional): SVG icon path

#### Usage

```blade
{{-- Primary button --}}
<x-button variant="primary">Save</x-button>

{{-- Button with icon --}}
<x-button variant="success" :icon="'<path d=\"M5 13l4 4L19 7\"/>'">
  Success
</x-button>

{{-- Button as link --}}
<x-button variant="outline" :href="route('users.index')">
  Back to List
</x-button>

{{-- Different sizes --}}
<x-button size="sm">Small</x-button>
<x-button size="md">Medium</x-button>
<x-button size="lg">Large</x-button>

{{-- Different variants --}}
<x-button variant="primary">Primary</x-button>
<x-button variant="secondary">Secondary</x-button>
<x-button variant="success">Success</x-button>
<x-button variant="danger">Danger</x-button>
<x-button variant="warning">Warning</x-button>
<x-button variant="outline">Outline</x-button>
```

### Badge Component

**File:** `resources/views/components/badge.blade.php`

Status and label badge component.

#### Props

- `variant` (string, default: 'primary'): Badge color (primary, success, danger, warning, info, gray, purple, pink)
- `size` (string, default: 'md'): Badge size (sm, md, lg)
- `rounded` (boolean, default: true): Rounded corners

#### Usage

```blade
{{-- Basic badge --}}
<x-badge>Default</x-badge>

{{-- Status badges --}}
<x-badge variant="success">Active</x-badge>
<x-badge variant="danger">Inactive</x-badge>
<x-badge variant="warning">Pending</x-badge>

{{-- Different sizes --}}
<x-badge size="sm">Small</x-badge>
<x-badge size="md">Medium</x-badge>
<x-badge size="lg">Large</x-badge>

{{-- Square badge --}}
<x-badge :rounded="false">Square</x-badge>
```

### Card Component

**File:** `resources/views/components/card.blade.php`

Container card component with shadow and border.

#### Props

- `padding` (boolean, default: true): Add padding
- `shadow` (boolean, default: true): Add shadow

#### Usage

```blade
{{-- Basic card --}}
<x-card>
  <h3>Card Title</h3>
  <p>Card content goes here.</p>
</x-card>

{{-- Card without padding --}}
<x-card :padding="false">
  <img src="image.jpg" alt="Image" class="w-full rounded-t-xl">
  <div class="p-6">
    <h3>Card Title</h3>
    <p>Card content.</p>
  </div>
</x-card>

{{-- Card without shadow --}}
<x-card :shadow="false">
  <p>Flat card design.</p>
</x-card>
```

### Alert Component

**File:** `resources/views/components/alert.blade.php`

Alert message component for notifications.

#### Props

- `variant` (string, default: 'info'): Alert type (info, success, warning, danger)
- `dismissible` (boolean, default: false): Show close button

#### Usage

```blade
{{-- Info alert --}}
<x-alert variant="info">
  This is an informational message.
</x-alert>

{{-- Success alert --}}
<x-alert variant="success">
  Operation completed successfully!
</x-alert>

{{-- Dismissible alert --}}
<x-alert variant="warning" dismissible>
  This alert can be dismissed.
</x-alert>
```

---

## 📐 Layout Components

### Table Component

**File:** `resources/views/components/table.blade.php`

Styled table component with headers and responsive design.

#### Props

- `headers` (array, default: []): Table headers
- `hoverable` (boolean, default: true): Row hover effect
- `striped` (boolean, default: false): Striped rows

#### Usage

```blade
{{-- Basic table --}}
<x-table :headers="['Name', 'Email', 'Role', 'Actions']">
  @foreach($users as $user)
    <tr>
      <td class="px-6 py-4">{{ $user->name }}</td>
      <td class="px-6 py-4">{{ $user->email }}</td>
      <td class="px-6 py-4">{{ $user->role }}</td>
      <td class="px-6 py-4">
        <x-button size="sm">Edit</x-button>
      </td>
    </tr>
  @endforeach
</x-table>

{{-- Striped table --}}
<x-table :headers="['ID', 'Name']" striped>
  {{-- Table rows --}}
</x-table>
```

### Pagination Component

**File:** `resources/views/components/pagination.blade.php`

Pagination component for Laravel paginators.

#### Props

- `paginator` (LengthAwarePaginator, required): Laravel paginator instance

#### Usage

```blade
{{-- Basic pagination --}}
<x-pagination :paginator="$users" />

{{-- In table context --}}
<x-table :headers="['Name', 'Email']">
  {{-- Table content --}}
</x-table>
<x-pagination :paginator="$users" />
```

### Stat Card Component

**File:** `resources/views/components/stat-card.blade.php`

Statistics display card component.

#### Props

- `title` (string, required): Stat title
- `value` (string|number, required): Stat value
- `color` (string, default: 'blue'): Card color theme
- `icon` (string, optional): SVG icon path
- `trend` (string, optional): Trend indicator
- `trendUp` (boolean, default: true): Trend direction

#### Usage

```blade
{{-- Basic stat card --}}
<x-stat-card title="Total Users" :value="$totalUsers" />

{{-- Stat card with icon and trend --}}
<x-stat-card
  title="Revenue"
  :value="$revenue"
  color="green"
  trend="+12%"
  :trend-up="true"
  :icon="'<path d=\"M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1\"/>'"
/>
```

---

## ⚡ Interactive Components

### Dropdown Component

**File:** `resources/views/components/dropdown.blade.php`

Alpine.js-powered dropdown component.

#### Props

- `align` (string, default: 'right'): Dropdown alignment (left, right, top)
- `width` (string, default: '48'): Dropdown width (48, 56, 64)
- `trigger` (slot, optional): Custom trigger element

#### Usage

```blade
{{-- Basic dropdown --}}
<x-dropdown>
  <x-slot name="trigger">
    <button>Options</button>
  </x-slot>

  <x-dropdown-link :href="route('users.show', $user->id)">
    View
  </x-dropdown-link>
  <x-dropdown-link :href="route('users.edit', $user->id)">
    Edit
  </x-dropdown-link>
  <x-dropdown-divider />
  <x-dropdown-button variant="danger">
    Delete
  </x-dropdown-button>
</x-dropdown>

{{-- Dropdown with custom alignment --}}
<x-dropdown align="left" width="56">
  {{-- Dropdown content --}}
</x-dropdown>
```

### Dropdown Link Component

**File:** `resources/views/components/dropdown-link.blade.php`

Link item for dropdown menus.

#### Props

- `href` (string, default: '#'): Link URL
- `icon` (string, optional): SVG icon path
- `variant` (string, default: 'default'): Link style (default, danger, success, warning)

#### Usage

```blade
{{-- Basic dropdown link --}}
<x-dropdown-link :href="route('users.show', $user->id)">
  View Details
</x-dropdown-link>

{{-- Link with icon --}}
<x-dropdown-link
  :href="route('users.edit', $user->id)"
  :icon="'<path d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"/>'"
>
  Edit User
</x-dropdown-link>

{{-- Danger variant --}}
<x-dropdown-link variant="danger" :href="route('users.destroy', $user->id)">
  Delete User
</x-dropdown-link>
```

### Dropdown Button Component

**File:** `resources/views/components/dropdown-button.blade.php`

Button item for dropdown menus.

#### Props

- `type` (string, default: 'button'): Button type
- `icon` (string, optional): SVG icon path
- `variant` (string, default: 'default'): Button style (default, danger, success, warning)

#### Usage

```blade
{{-- Basic dropdown button --}}
<x-dropdown-button type="button" onclick="doSomething()">
  Action
</x-dropdown-button>

{{-- Button with icon --}}
<x-dropdown-button
  type="button"
  variant="danger"
  :icon="'<path d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"/>'"
>
  Delete User
</x-dropdown-button>
```

### Dropdown Divider Component

**File:** `resources/views/components/dropdown-divider.blade.php`

Separator for dropdown menus.

#### Usage

```blade
<x-dropdown>
  <x-dropdown-link :href="route('users.show', $user->id)">View</x-dropdown-link>
  <x-dropdown-link :href="route('users.edit', $user->id)">Edit</x-dropdown-link>

  <x-dropdown-divider />

  <x-dropdown-button variant="danger">Delete</x-dropdown-button>
</x-dropdown>
```

### Action Dropdown Component

**File:** `resources/views/components/action-dropdown.blade.php`

Pre-configured dropdown for table actions (3-dots menu).

#### Props

- `align` (string, default: 'right'): Dropdown alignment

#### Usage

```blade
{{-- In table row --}}
<td class="px-6 py-4 whitespace-nowrap text-right">
  <x-action-dropdown>
    <x-dropdown-link :href="route('users.show', $user->id)">
      👁️ View Details
    </x-dropdown-link>
    <x-dropdown-link :href="route('users.edit', $user->id)">
      ✏️ Edit User
    </x-dropdown-link>
    <x-dropdown-divider />
    <x-dropdown-button variant="danger" onclick="confirmDelete({{ $user->id }})">
      🗑️ Delete User
    </x-dropdown-button>
  </x-action-dropdown>
</td>
```

### Modal Component

**File:** `resources/views/components/modal.blade.php`

Alpine.js-powered modal component.

#### Props

- `id` (string, required): Modal unique identifier
- `title` (string, default: ''): Modal title
- `size` (string, default: 'md'): Modal size (sm, md, lg, xl)
- `footer` (boolean, default: true): Show footer slot

#### Usage

```blade
{{-- Basic modal --}}
<x-modal id="confirm-delete" title="Confirm Delete">
  <p>Are you sure you want to delete this item?</p>

  <x-slot name="footer">
    <x-button variant="secondary" @click="$dispatch('close-modal-confirm-delete')">
      Cancel
    </x-button>
    <x-button variant="danger">Delete</x-button>
  </x-slot>
</x-modal>

{{-- Trigger modal --}}
<x-button @click="$dispatch('open-modal-confirm-delete')">
  Open Modal
</x-button>

{{-- Large modal --}}
<x-modal id="user-details" title="User Details" size="lg">
  {{-- Modal content --}}
</x-modal>
```

### Spinner Component

**File:** `resources/views/components/spinner.blade.php`

Loading spinner component.

#### Props

- `size` (string, default: 'md'): Spinner size (sm, md, lg, xl)
- `color` (string, default: 'blue'): Spinner color (blue, gray, white, green, red)

#### Usage

```blade
{{-- Basic spinner --}}
<x-spinner />

{{-- Large blue spinner --}}
<x-spinner size="lg" color="blue" />

{{-- Small white spinner --}}
<x-spinner size="sm" color="white" />
```

---

## 🎯 Component Usage Examples

### Complete Form Example

```blade
<x-card>
  <div class="p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">User Information</h3>

    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
      @csrf

      <x-input
        name="name"
        label="Full Name"
        placeholder="Enter full name"
        required
      />

      <x-input
        type="email"
        name="email"
        label="Email Address"
        placeholder="user@example.com"
        required
        hint="This email will be used for login"
      />

      <x-input
        type="password"
        name="password"
        label="Password"
        placeholder="Enter password"
        required
        hint="Password must be at least 6 characters"
      />

      <x-input
        type="password"
        name="password_confirmation"
        label="Confirm Password"
        placeholder="Re-enter password"
        required
      />

      <x-select
        name="role"
        label="User Role"
        :options="['user' => 'Regular User', 'admin' => 'Administrator']"
        placeholder="Select a role"
        required
        hint="Choose the appropriate role for this user"
      />

      <x-checkbox name="terms" label="I agree to the terms and conditions" />

      <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
        <x-button variant="secondary" :href="route('users.index')">
          Cancel
        </x-button>
        <x-button type="submit" variant="primary">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Create User
        </x-button>
      </div>
    </form>
  </div>
</x-card>
```

### Data Table Example

```blade
<x-card>
  <div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Users</h3>
      <x-button variant="primary" size="sm" :href="route('users.create')">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add User
      </x-button>
    </div>
  </div>

  <x-table :headers="['Name', 'Email', 'Role', 'Created', 'Actions']">
    @forelse($users as $user)
      <tr class="hover:bg-blue-50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md">
              <span class="text-sm font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="text-sm text-gray-900">{{ $user->email }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <x-badge :variant="$user->isAdmin() ? 'purple' : 'primary'">
            {{ ucfirst($user->role) }}
          </x-badge>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          {{ $user->created_at->format('M d, Y') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
          <x-action-dropdown>
            <x-dropdown-link :href="route('users.show', $user->id)">
              👁️ View Details
            </x-dropdown-link>
            <x-dropdown-link :href="route('users.edit', $user->id)">
              ✏️ Edit User
            </x-dropdown-link>
            @if ($user->id !== auth()->id())
              <x-dropdown-divider />
              <x-dropdown-button type="button" onclick="confirmDelete({{ $user->id }})" variant="danger">
                🗑️ Delete User
              </x-dropdown-button>
            @endif
          </x-action-dropdown>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="5" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center justify-center">
            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p class="text-gray-500 text-lg font-medium">No users found</p>
            <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
          </div>
        </td>
      </tr>
    @endforelse
  </x-table>

  @if ($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
      <x-pagination :paginator="$users" />
    </div>
  @endif
</x-card>
```

### Dashboard Stats Example

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
  <x-stat-card
    title="Total Users"
    :value="$totalUsers"
    color="blue"
    :icon="'<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\"/>'"
    trend="+12%"
    :trend-up="true"
  />

  <x-stat-card
    title="Active Sessions"
    :value="$activeSessions"
    color="green"
    :icon="'<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"/>'"
    trend="+5%"
    :trend-up="true"
  />

  <x-stat-card
    title="Error Rate"
    :value="$errorRate"
    color="red"
    :icon="'<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\"/>'"
    trend="-2%"
    :trend-up="false"
  />

  <x-stat-card
    title="Revenue"
    :value="$revenue"
    color="purple"
    :icon="'<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1\"/>'"
    trend="+18%"
    :trend-up="true"
  />
</div>
```

---

## 🎨 Styling Guidelines

### Color System

- **Primary**: Blue shades for main actions
- **Success**: Green shades for positive actions
- **Danger**: Red shades for destructive actions
- **Warning**: Yellow shades for caution
- **Info**: Cyan shades for information
- **Gray**: Neutral shades for secondary content

### Spacing

- Use consistent spacing with Tailwind's spacing scale
- Form elements: `space-y-1.5` for vertical spacing
- Cards: `p-6` for padding
- Buttons: `px-4 py-2` for medium size

### Typography

- Labels: `text-sm font-medium`
- Input text: `text-sm`
- Headings: `text-lg font-semibold` or `text-xl font-bold`
- Body text: `text-sm` or `text-base`

### Responsive Design

- Mobile-first approach
- Use responsive prefixes: `sm:`, `md:`, `lg:`, `xl:`
- Grid layouts: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`

---

## 🔧 Customization

### Creating Custom Components

1. Create component file in `resources/views/components/`
2. Use `@props()` to define component properties
3. Follow naming convention: `kebab-case.blade.php`
4. Use consistent styling with design system

### Extending Existing Components

```blade
{{-- Extend button component --}}
<x-button
  variant="primary"
  class="custom-class"
  data-custom="value"
>
  Custom Button
</x-button>
```

### Component Composition

```blade
{{-- Compose complex UI with multiple components --}}
<x-card>
  <div class="flex items-center justify-between mb-4">
    <h3 class="text-lg font-semibold">User Profile</h3>
    <x-badge variant="success">Active</x-badge>
  </div>

  <div class="space-y-4">
    <x-input name="name" label="Name" :value="$user->name" />
    <x-input name="email" label="Email" :value="$user->email" />
    <x-toggle name="notifications" label="Email Notifications" checked />
  </div>

  <div class="flex justify-end space-x-3 mt-6">
    <x-button variant="secondary">Cancel</x-button>
    <x-button variant="primary">Save Changes</x-button>
  </div>
</x-card>
```

---

This component guide provides comprehensive documentation for all reusable components in the CCTV Dashboard. Each component is designed to be flexible, accessible, and consistent with the overall design system.
