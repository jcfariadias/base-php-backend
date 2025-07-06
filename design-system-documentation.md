# Design System Documentation
## Warehouse.space SME Fulfillment Platform

### Version 1.0
### Last Updated: July 2025

---

## Table of Contents

1. [Design Principles](#design-principles)
2. [Visual Identity](#visual-identity)
3. [Typography](#typography)
4. [Color System](#color-system)
5. [Spacing & Layout](#spacing--layout)
6. [Component Library](#component-library)
7. [Icon System](#icon-system)
8. [Motion & Animation](#motion--animation)
9. [Accessibility Guidelines](#accessibility-guidelines)
10. [Implementation Guidelines](#implementation-guidelines)

---

## Design Principles

### 1. Accessibility First
**"Every user should be able to use our platform effectively"**
- Design for WCAG 2.1 AA compliance
- Consider users with disabilities, slow internet, and older devices
- Provide multiple ways to complete tasks

### 2. Transparency Over Complexity
**"Show, don't hide - make everything clear and understandable"**
- Display costs upfront, no hidden fees
- Show process status at every step
- Use plain language instead of technical jargon

### 3. Mobile-First Simplicity
**"Design for the small screen first, enhance for larger screens"**
- Core functionality works on mobile
- Touch-friendly interactions
- Essential information always visible

### 4. Progressive Enhancement
**"Start simple, add complexity as users grow"**
- Basic features for new users
- Advanced features unlocked as needed
- Contextual help and guidance

### 5. Human-Centered Design
**"Technology serves people, not the other way around"**
- Reduce cognitive load
- Celebrate user successes
- Provide helpful error messages

---

## Visual Identity

### Brand Personality
- **Approachable**: Friendly but professional
- **Reliable**: Trustworthy without being corporate
- **Efficient**: Fast and streamlined
- **Transparent**: Open and honest

### Logo Usage

#### Primary Logo
```
[ Warehouse.space ]
```
- Minimum size: 120px width (digital), 1 inch (print)
- Clear space: 1x logo height on all sides
- Use on light backgrounds

#### Logo Variations
- **Icon Only**: For small spaces, app icons
- **Stacked Version**: For narrow layouts
- **Monochrome**: For single-color applications

### Brand Voice
- **Tone**: Friendly, confident, helpful
- **Language**: Plain English, avoid jargon
- **Personality**: Like a knowledgeable friend helping you grow

---

## Typography

### Primary Typeface: Inter

**Why Inter?**
- Excellent legibility at all sizes
- Wide character support
- Optimized for screens
- Free and open source

### Type Scale

```css
/* Heading Styles */
.h1 { font-size: 32px; line-height: 40px; font-weight: 600; }
.h2 { font-size: 24px; line-height: 32px; font-weight: 600; }
.h3 { font-size: 20px; line-height: 28px; font-weight: 500; }
.h4 { font-size: 18px; line-height: 26px; font-weight: 500; }

/* Body Styles */
.body-large { font-size: 18px; line-height: 28px; font-weight: 400; }
.body-base { font-size: 16px; line-height: 24px; font-weight: 400; }
.body-small { font-size: 14px; line-height: 20px; font-weight: 400; }

/* Utility Styles */
.caption { font-size: 12px; line-height: 16px; font-weight: 500; }
.overline { font-size: 11px; line-height: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
```

### Typography Guidelines

**Hierarchy**
- Use consistent heading levels (H1 > H2 > H3)
- Don't skip heading levels
- One H1 per page

**Readability**
- Line length: 45-75 characters
- Line height: 1.4-1.6 for body text
- Sufficient contrast (4.5:1 minimum)

**Weight Usage**
- **600 (Semi-bold)**: Headings, important actions
- **500 (Medium)**: Subheadings, labels
- **400 (Regular)**: Body text, descriptions

---

## Color System

### Primary Colors

```css
/* Primary Green - Trust, Growth, Success */
--green-50: #ECFDF5;
--green-100: #D1FAE5;
--green-500: #10B981;  /* Primary brand color */
--green-600: #059669;
--green-700: #047857;
--green-900: #064E3B;

/* Secondary Blue - Reliability, Technology */
--blue-50: #EFF6FF;
--blue-100: #DBEAFE;
--blue-500: #3B82F6;
--blue-600: #2563EB;
--blue-700: #1D4ED8;
--blue-900: #1E3A8A;

/* Accent Orange - Energy, Action, Alerts */
--orange-50: #FFFBEB;
--orange-100: #FEF3C7;
--orange-500: #F59E0B;
--orange-600: #D97706;
--orange-700: #B45309;
--orange-900: #92400E;
```

### Neutral Colors

```css
/* Grays */
--gray-50: #FAFAFA;   /* Background */
--gray-100: #F4F4F5;  /* Card backgrounds */
--gray-200: #E4E4E7;  /* Borders */
--gray-300: #D4D4D8;  /* Disabled states */
--gray-400: #A1A1AA;  /* Placeholder text */
--gray-500: #71717A;  /* Secondary text */
--gray-700: #374151;  /* Primary text */
--gray-900: #1F2937;  /* Headings */
```

### Status Colors

```css
/* Success */
--success-bg: #ECFDF5;
--success-border: #10B981;
--success-text: #047857;

/* Warning */
--warning-bg: #FFFBEB;
--warning-border: #F59E0B;
--warning-text: #92400E;

/* Error */
--error-bg: #FEF2F2;
--error-border: #EF4444;
--error-text: #DC2626;

/* Info */
--info-bg: #EFF6FF;
--info-border: #3B82F6;
--info-text: #1D4ED8;
```

### Color Usage Guidelines

**Primary Green (#10B981)**
- Primary buttons and CTAs
- Success states
- Progress indicators
- Brand elements

**Blue (#3B82F6)**
- Links and secondary actions
- Information highlights
- Navigation active states

**Orange (#F59E0B)**
- Warnings and attention
- Promotional elements
- Notifications

**When to Use Each Color**
- **Green**: Positive actions, confirmations, success
- **Blue**: Navigation, information, secondary actions
- **Orange**: Warnings, promotions, energy
- **Red**: Errors, dangerous actions, urgent alerts
- **Gray**: Text, backgrounds, borders, disabled states

---

## Spacing & Layout

### Spacing Scale (8px base unit)

```css
--space-0: 0px;
--space-1: 4px;    /* 0.5 units */
--space-2: 8px;    /* 1 unit - base */
--space-3: 12px;   /* 1.5 units */
--space-4: 16px;   /* 2 units */
--space-5: 20px;   /* 2.5 units */
--space-6: 24px;   /* 3 units */
--space-8: 32px;   /* 4 units */
--space-10: 40px;  /* 5 units */
--space-12: 48px;  /* 6 units */
--space-16: 64px;  /* 8 units */
--space-20: 80px;  /* 10 units */
--space-24: 96px;  /* 12 units */
```

### Layout Guidelines

**Container Widths**
```css
--container-sm: 640px;    /* Mobile landscape */
--container-md: 768px;    /* Tablet */
--container-lg: 1024px;   /* Desktop */
--container-xl: 1280px;   /* Large desktop */
```

**Breakpoints**
```css
--bp-sm: 640px;   /* Mobile landscape */
--bp-md: 768px;   /* Tablet */
--bp-lg: 1024px;  /* Desktop */
--bp-xl: 1280px;  /* Large desktop */
```

**Grid System**
- 12-column grid on desktop
- 4-column grid on mobile
- 24px gutters on desktop
- 16px gutters on mobile

---

## Component Library

### Buttons

#### Primary Button
```css
.btn-primary {
  background: var(--green-500);
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 16px;
  border: none;
  min-height: 44px;
  cursor: pointer;
}

.btn-primary:hover {
  background: var(--green-600);
}

.btn-primary:disabled {
  background: var(--gray-300);
  cursor: not-allowed;
}
```

#### Secondary Button
```css
.btn-secondary {
  background: transparent;
  color: var(--green-600);
  border: 2px solid var(--green-500);
  padding: 10px 22px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 16px;
  min-height: 44px;
  cursor: pointer;
}
```

#### Button Sizes
- **Large**: 48px height, 20px padding
- **Medium**: 44px height, 16px padding (default)
- **Small**: 36px height, 12px padding

### Cards

#### Basic Card
```css
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
  border: 1px solid var(--gray-200);
}

.card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
```

#### Card with Header
```html
<div class="card">
  <div class="card-header">
    <h3>Card Title</h3>
    <button class="btn-text">Action</button>
  </div>
  <div class="card-content">
    <p>Card content goes here...</p>
  </div>
</div>
```

### Form Elements

#### Input Fields
```css
.input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid var(--gray-200);
  border-radius: 8px;
  font-size: 16px;
  line-height: 24px;
  min-height: 44px;
}

.input:focus {
  outline: none;
  border-color: var(--blue-500);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input.error {
  border-color: var(--error-border);
}
```

#### Select Dropdown
```css
.select {
  position: relative;
  width: 100%;
}

.select select {
  width: 100%;
  padding: 12px 40px 12px 16px;
  border: 2px solid var(--gray-200);
  border-radius: 8px;
  font-size: 16px;
  background: white;
  appearance: none;
  min-height: 44px;
}
```

#### Checkbox & Radio
```css
.checkbox, .radio {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  cursor: pointer;
  min-height: 44px;
}

.checkbox input, .radio input {
  width: 20px;
  height: 20px;
  margin: 0;
}
```

### Navigation

#### Tab Navigation
```css
.tabs {
  display: flex;
  border-bottom: 2px solid var(--gray-200);
  gap: 32px;
}

.tab {
  padding: 12px 0;
  font-weight: 500;
  color: var(--gray-500);
  border-bottom: 2px solid transparent;
  cursor: pointer;
  min-height: 44px;
}

.tab.active {
  color: var(--blue-600);
  border-bottom-color: var(--blue-500);
}
```

### Status Indicators

#### Badges
```css
.badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge.success {
  background: var(--success-bg);
  color: var(--success-text);
}

.badge.warning {
  background: var(--warning-bg);
  color: var(--warning-text);
}
```

#### Progress Indicators
```css
.progress {
  width: 100%;
  height: 8px;
  background: var(--gray-200);
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: var(--green-500);
  transition: width 0.3s ease;
}
```

### Data Display

#### Tables
```css
.table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table th {
  background: var(--gray-50);
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: var(--gray-700);
  border-bottom: 2px solid var(--gray-200);
}

.table td {
  padding: 16px;
  border-bottom: 1px solid var(--gray-100);
}
```

---

## Icon System

### Icon Guidelines

**Style**: Outline style icons with 2px stroke width
**Size**: 16px, 20px, 24px standard sizes
**Source**: Heroicons or custom SVGs
**Usage**: Always include proper alt text

### Common Icons

**Navigation & Actions**
- Menu: ☰
- Close: ✕
- Back: ←
- Forward: →
- Search: 🔍
- Settings: ⚙️

**Status & Feedback**
- Success: ✅
- Warning: ⚠️
- Error: ❌
- Info: ℹ️
- Loading: ⟳

**E-commerce Specific**
- Orders: 📦
- Inventory: 📊
- Shipping: 🚚
- Analytics: 📈
- Money: 💰

### Icon Implementation

```html
<!-- Inline SVG with proper accessibility -->
<svg class="icon icon-20" aria-hidden="true">
  <use href="#icon-package"></use>
</svg>

<!-- With descriptive text -->
<span class="icon-text">
  <svg class="icon icon-16" aria-hidden="true">
    <use href="#icon-check"></use>
  </svg>
  Order Complete
</span>
```

---

## Motion & Animation

### Animation Principles

**Purpose-Driven**
- Only animate to provide feedback or guide attention
- Avoid gratuitous animations

**Fast and Subtle**
- Duration: 150-300ms for micro-interactions
- Easing: `ease-out` for entries, `ease-in` for exits

**Accessible**
- Respect `prefers-reduced-motion`
- Provide alternative feedback methods

### Animation Types

#### Micro-interactions
```css
/* Button hover */
.btn {
  transition: all 0.15s ease-out;
}

/* Loading states */
.loading {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
```

#### Page Transitions
```css
/* Slide in from right */
.slide-enter {
  transform: translateX(100%);
  opacity: 0;
}

.slide-enter-active {
  transform: translateX(0);
  opacity: 1;
  transition: all 0.3s ease-out;
}
```

#### Feedback Animations
```css
/* Success celebration */
.success-bounce {
  animation: bounce 0.6s ease-out;
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0,0,0);
  }
  40%, 43% {
    transform: translate3d(0, -8px, 0);
  }
  70% {
    transform: translate3d(0, -4px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}
```

---

## Accessibility Guidelines

### WCAG 2.1 AA Compliance

#### Color Contrast
- Normal text: 4.5:1 minimum
- Large text (18px+): 3:1 minimum
- UI components: 3:1 minimum

#### Keyboard Navigation
- All interactive elements must be keyboard accessible
- Logical tab order
- Visible focus indicators
- Skip navigation links

#### Screen Reader Support
- Semantic HTML structure
- Proper heading hierarchy
- Alt text for images
- ARIA labels and descriptions
- Form labels and error messages

#### Touch Accessibility
- Minimum 44px touch targets
- Adequate spacing between targets
- Swipe gestures have alternatives

### Implementation Checklist

**Every Component Must Have:**
- [ ] Proper semantic markup
- [ ] Keyboard accessibility
- [ ] Screen reader support
- [ ] Color contrast compliance
- [ ] Focus management
- [ ] Error handling
- [ ] Loading states
- [ ] Empty states

**Testing Requirements:**
- [ ] Keyboard-only navigation
- [ ] Screen reader testing
- [ ] Color blindness simulation
- [ ] Reduced motion testing
- [ ] Mobile accessibility testing

---

## Implementation Guidelines

### Development Standards

#### CSS Architecture
```css
/* Use CSS custom properties */
:root {
  --primary-color: #10B981;
  --border-radius: 8px;
}

/* Component-based naming */
.card { }
.card__header { }
.card__content { }
.card--featured { }
```

#### Responsive Design
```css
/* Mobile first approach */
.component {
  /* Mobile styles */
}

@media (min-width: 768px) {
  .component {
    /* Tablet styles */
  }
}

@media (min-width: 1024px) {
  .component {
    /* Desktop styles */
  }
}
```

#### Performance Guidelines
- Optimize images for different screen densities
- Use system fonts when possible
- Minimize animation usage
- Progressive enhancement approach

### Quality Assurance

#### Browser Support
- **Primary**: Chrome, Firefox, Safari, Edge (latest 2 versions)
- **Secondary**: Mobile Safari, Chrome Mobile
- **Graceful degradation**: IE11 (basic functionality only)

#### Testing Checklist
- [ ] Visual regression testing
- [ ] Accessibility testing
- [ ] Performance testing
- [ ] Cross-browser testing
- [ ] Mobile device testing
- [ ] Keyboard navigation testing

#### Documentation Requirements
- Component usage examples
- Accessibility considerations
- Browser support notes
- Performance implications

---

## Design System Maintenance

### Version Control
- Semantic versioning (Major.Minor.Patch)
- Change log maintenance
- Migration guides for breaking changes

### Governance
- Design system team ownership
- Regular audits and updates
- Component usage tracking
- User feedback collection

### Evolution Process
1. **Identify Need**: User research, design debt, new requirements
2. **Propose Solution**: Design exploration, technical feasibility
3. **Review & Approve**: Cross-team review, accessibility check
4. **Implement**: Development, testing, documentation
5. **Deploy**: Gradual rollout, monitoring, feedback collection

---

*This design system documentation is a living document that will evolve with our platform and user needs. For questions or suggestions, contact the UX/UI team.*