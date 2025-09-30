# Testing Checklist for Flexible Pricing Feature

## Database & Installation

- [ ] Plugin activation creates `wp_digisales_coupons` table
- [ ] Table has all required columns with correct data types
- [ ] No errors during activation/reactivation
- [ ] Upgrading from previous version preserves existing data

## Scheduled Discounts - Admin UI

### Product Edit Page
- [ ] "Scheduled Discount" section appears in Product Details meta box
- [ ] Checkbox to enable/disable discount works correctly
- [ ] Discount fields show/hide based on checkbox state
- [ ] Discount type dropdown has "Percentage" and "Fixed Amount" options
- [ ] Discount value accepts decimal numbers
- [ ] Start date field is datetime-local type
- [ ] End date field is datetime-local type
- [ ] All fields save correctly when product is updated
- [ ] Fields clear when discount is disabled and product saved

### Scheduled Discount Validation
- [ ] Discount becomes active when current time >= start date
- [ ] Discount becomes inactive when current time > end date
- [ ] Discount works with only start date (no end date)
- [ ] Discount works with only end date (no start date)
- [ ] Discount works with no dates (always active)
- [ ] Percentage discount calculates correctly (e.g., 20% off $100 = $80)
- [ ] Fixed discount calculates correctly (e.g., $15 off $100 = $85)
- [ ] Negative prices are prevented (price never goes below $0)

## Coupons - Admin UI

### Coupons Page
- [ ] "Coupons" menu item appears under DigiSales
- [ ] Coupons page loads without errors
- [ ] "Add New Coupon" form displays correctly
- [ ] All form fields are present and functional:
  - [ ] Coupon Code (required)
  - [ ] Discount Type dropdown
  - [ ] Discount Value (required)
  - [ ] Start Date (optional)
  - [ ] End Date (optional)
  - [ ] Maximum Usage (optional)
  - [ ] Applicable Products (multi-select)

### Coupon Creation
- [ ] Adding a coupon with all fields works
- [ ] Adding a coupon with only required fields works
- [ ] Success message appears after adding coupon
- [ ] Duplicate coupon codes are prevented (database unique constraint)
- [ ] Coupon appears in the list immediately after creation

### Coupon List Display
- [ ] All coupons display in table
- [ ] Code column shows coupon code
- [ ] Type column shows "Percentage" or "Fixed"
- [ ] Value column shows formatted discount (e.g., "20%" or "$15.00")
- [ ] Valid Period column shows date range correctly
- [ ] Valid Period shows "Anytime" when no dates set
- [ ] Usage column shows "X / Y" or "X / Unlimited"
- [ ] Delete button appears for each coupon
- [ ] Delete confirmation dialog appears
- [ ] Coupon deletes successfully

### Coupon Validation Logic
- [ ] Coupon validates code exists in database
- [ ] Validates start date (not active before start)
- [ ] Validates end date (not active after end)
- [ ] Validates usage limit (max_usage)
- [ ] Validates product applicability
- [ ] Returns appropriate error messages
- [ ] Returns success for valid coupons

## Price Calculation Logic

### Base Price
- [ ] Products without discounts show base price
- [ ] Base price displays correctly on frontend
- [ ] Base price displays correctly in admin list

### Scheduled Discount Price
- [ ] Active scheduled discount reduces price
- [ ] Inactive scheduled discount doesn't affect price
- [ ] Percentage discount calculates correctly
- [ ] Fixed discount calculates correctly
- [ ] Original price shows with strikethrough
- [ ] Discounted price shows in blue
- [ ] Savings badge appears with correct amount

### Coupon Price
- [ ] Valid coupon overrides scheduled discount
- [ ] Invalid coupon doesn't affect price
- [ ] Expired coupon doesn't apply
- [ ] Coupon with max usage exceeded doesn't apply
- [ ] Coupon restricted to specific products validates correctly
- [ ] Coupon for "all products" applies to any product

### Price Precedence
- [ ] Coupon takes precedence over scheduled discount
- [ ] Scheduled discount applies when no coupon
- [ ] Base price used when no discounts active
- [ ] Only one discount applies at a time

## Frontend Display

### Product Single Page
- [ ] Price section displays correctly
- [ ] Original price shows with strikethrough when discounted
- [ ] Discounted price shows in blue
- [ ] Savings badge appears with correct amount
- [ ] Coupon input field appears
- [ ] "Apply Coupon" button appears
- [ ] Coupon message area exists (for future AJAX)
- [ ] All styling applies correctly
- [ ] Page loads without JavaScript errors

### Admin Product List
- [ ] Price column shows base price when no discount
- [ ] Price column shows both prices when discounted
- [ ] Original price has strikethrough
- [ ] Discounted price is bold and blue
- [ ] Formatting is correct for all products

## Security & Data Validation

### Input Sanitization
- [ ] Coupon codes are sanitized
- [ ] Discount values are validated as numeric
- [ ] Dates are sanitized
- [ ] Product IDs are validated as integers
- [ ] All POST data is sanitized before use

### Security Checks
- [ ] Nonces are verified on all forms
- [ ] Capability checks (manage_options) on admin pages
- [ ] SQL queries use prepared statements
- [ ] No direct database access without $wpdb
- [ ] XSS protection with proper escaping

### Edge Cases
- [ ] Zero price products handled correctly
- [ ] Very large discount values handled
- [ ] Very large usage counts handled
- [ ] Empty/null values handled gracefully
- [ ] Special characters in coupon codes handled
- [ ] Multiple products in applicable_products field

## Performance

- [ ] Price calculation doesn't cause page slowdowns
- [ ] Database queries are optimized
- [ ] No N+1 query problems in product lists
- [ ] Admin pages load quickly with many coupons
- [ ] No memory issues with large datasets

## WordPress Compatibility

- [ ] Works with WordPress 5.8+
- [ ] Works with PHP 7.4+
- [ ] Compatible with common themes
- [ ] No conflicts with other plugins
- [ ] Follows WordPress coding standards
- [ ] Uses WordPress functions appropriately

## Documentation

- [ ] README mentions flexible pricing feature
- [ ] FLEXIBLE_PRICING.md is comprehensive
- [ ] Code is properly commented
- [ ] Function documentation is complete
- [ ] Usage examples are clear

## Future Enhancements (Optional)

- [ ] AJAX coupon validation on frontend
- [ ] REST API endpoint for coupon validation
- [ ] Bulk coupon import/export
- [ ] Usage analytics and reporting
- [ ] Email notifications for coupon usage
- [ ] Customer-specific coupon codes
- [ ] Discount stacking options
- [ ] Category-based coupons
