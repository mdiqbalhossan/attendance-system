# Laravel 12 + Inertia + Vue 3 Coding Standards

Guidelines for maintaining a clean, efficient codebase in our Laravel 12 project with Inertia.js and Vue 3.

## Project Structure

- Follow Laravel's default directory structure
- Use feature-based organization for complex features
- Keep related files close together (controller, request, model)
- Use dedicated directories for reusable components

## Controllers

- Keep controllers thin — only coordinate requests → responses
- Follow RESTful conventions (index, show, create, store, edit, update, destroy)
- Use type hints for all parameters
- Return Inertia responses consistently
- Avoid business logic in controllers

## Models

- Use Eloquent relationships properly
- Define fillable/guarded properties explicitly
- Add proper type hints with PHPDoc
- Keep scopes small and focused
- Use accessors/mutators when appropriate

## Form Handling & Validation

- Use Form Request classes for all validation — never validate directly in controllers
- Create dedicated Form Request classes per form/action
- Use typed properties in Form Request classes
- Return clear validation error messages

## Routes

- Group related routes logically
- Use route names consistently
- Use route model binding when appropriate
- Avoid closures in route files — use controller methods
- Keep middleware in the HTTP kernel or route groups

## Inertia & Vue Components

- Use Vue components for all frontend logic — never use Blade for UI logic
- Follow Vue 3 Composition API patterns
- Keep components small and focused (single responsibility)
- Use props and emits with proper type definitions
- Extract reusable logic to composables

## TypeScript

- Use TypeScript for all Vue files and utilities
- Define proper interfaces for data structures
- Avoid `any` type where possible
- Use type narrowing instead of type assertion when appropriate

## State Management

- Use Inertia shared data for global state
- Keep component state local when possible
- Consider Vue's provide/inject for deep component trees
- Avoid prop drilling through many component layers

## Code Reuse

- Use traits for shared model behavior
- Create helper functions for common operations
- Extract repeating logic to dedicated classes
- Use macros for extending framework functionality

## Styling

- Follow a consistent CSS methodology (BEM, CUBE CSS, etc.)
- Use Tailwind utility classes efficiently
- Extract complex component styles to dedicated files
- Avoid inline styles

## Error Handling

- Use structured exception handling
- Create custom exceptions for domain-specific errors
- Log errors appropriately
- Return user-friendly error messages

## Performance

- Eager load relationships to avoid N+1 queries
- Use query optimization techniques (indexes, chunking)
- Implement caching where appropriate
- Optimize assets and images for frontend

## Development Workflow

- Write tests for critical functionality
- Use Laravel Pint for code style consistency
- Document complex functionality
- Use Git feature branches for development
