# Flexible Pricing Feature Implementation

## Overview
The DigiSales plugin now supports a comprehensive flexible pricing system that includes:
1. Fixed base pricing (existing)
2. Scheduled discounts
3. Promotional coupon codes

## Features Implemented

### 1. Database Tables

#### Coupons Table (`wp_digisales_coupons`)
- `id` - Unique coupon identifier
- `code` - Unique coupon code string
- `discount_type` - Type of discount (percentage or fixed)
- `discount_value` - Discount amount/percentage
- `start_date` - Coupon validity start date (optional)
- `end_date` - Coupon validity end date (optional)
- `max_usage` - Maximum number of times coupon can be used (optional)
- `usage_count` - Current usage count
- `applicable_products` - Comma-separated product IDs (optional, empty = all products)
- `created_at` - Timestamp of creation

### 2. Product Scheduled Discounts

New product meta fields added:
- `_digisales_discount_enabled` - Boolean to enable/disable scheduled discount
- `_digisales_discount_type` - Discount type (percentage or fixed)
- `_digisales_discount_value` - Discount amount/percentage
- `_digisales_discount_start` - Discount start date and time
- `_digisales_discount_end` - Discount end date and time

### 3. Admin UI Components

#### Product Edit Page
- New "Scheduled Discount" section in Product Details meta box
- Toggle to enable/disable scheduled discounts
- Fields for discount type, value, start date, and end date
- Dynamic show/hide of discount fields based on toggle state

#### Coupons Management Page
- Accessible via DigiSales → Coupons menu
- Form to add new coupons with all required fields
- Table listing all existing coupons
- Display of coupon validity period and usage statistics
- Delete action for removing coupons

### 4. Pricing Calculation Logic

#### `calculate_product_price($product_id, $coupon_code = '')`
Calculates the final price with the following precedence:
1. Check for valid coupon code (highest priority)
2. Check for active scheduled discount
3. Use base price

Returns array with:
- `original_price` - Base product price
- `final_price` - Price after discounts
- `discount_amount` - Total discount applied
- `discount_source` - Source of discount ('coupon', 'scheduled', or '')

#### `validate_coupon($coupon_code, $product_id)`
Validates a coupon code for a specific product by checking:
- Coupon exists
- Current date is within validity period
- Usage limit not exceeded
- Product is in applicable products list (if specified)

Returns array with:
- `valid` - Boolean indicating validity
- `message` - User-friendly message
- `discount_info` - Discount type and value (when valid)

### 5. Frontend Display

#### Product Single Page
- Shows original price with strikethrough when discount is active
- Displays discounted price in blue
- Shows savings badge with discount amount
- Coupon code input field with "Apply Coupon" button
- Placeholder for coupon validation message

#### Admin Products List
- Price column now shows:
  - Original price (strikethrough) when discount is active
  - Discounted price in blue and bold

### 6. Discount Validation

Both scheduled discounts and coupons are validated for:
- **Date/Time validity**: Current time must be within start and end dates
- **Product applicability**: For coupons, checks if product is in allowed list
- **Usage limits**: For coupons, checks if max usage is not exceeded

## How to Use

### Setting Up a Scheduled Discount
1. Edit a digital product
2. Scroll to "Product Details" meta box
3. Check "Enable scheduled discount"
4. Select discount type (Percentage or Fixed Amount)
5. Enter discount value
6. Set start and end dates (optional - leave blank for always active)
7. Save/Update the product

### Creating a Coupon Code
1. Go to DigiSales → Coupons
2. Fill in the "Add New Coupon" form:
   - Coupon Code: Unique code customers will enter
   - Discount Type: Percentage or Fixed Amount
   - Discount Value: The discount amount/percentage
   - Start/End Dates: Optional validity period
   - Maximum Usage: Optional usage limit
   - Applicable Products: Select specific products or leave for all
3. Click "Add Coupon"

### Viewing Active Pricing
- Frontend: Visit any product page to see current pricing with discounts
- Admin: Check the Products list to see discounted prices
- Coupons: View the Coupons page to see all active coupons and their usage

## Price Precedence Rules

When multiple discounts could apply:
1. **Coupon Code** (if valid) - Takes highest precedence
2. **Scheduled Discount** (if active and no coupon applied)
3. **Base Price** (if no discounts apply)

Note: Currently, only one discount applies at a time. Coupons override scheduled discounts.

## Security Features

- All form submissions use WordPress nonces for CSRF protection
- Input sanitization on all user inputs
- SQL injection protection using `$wpdb->prepare()`
- Capability checks (manage_options required for admin pages)
- Proper escaping of output data

## Future Enhancements (Not Implemented)

The following could be added in future versions:
- Frontend AJAX coupon validation
- Coupon usage tracking per customer
- Bulk coupon creation
- Export/import coupons
- Discount stacking (applying multiple discounts)
- Customer-specific coupons
- Minimum purchase requirements
- Category-based coupons
- Usage analytics and reporting

## Technical Notes

- Database tables are created on plugin activation via `dbDelta()`
- All datetime fields use MySQL `datetime` format
- Pricing calculations handle edge cases (negative prices set to 0)
- Product meta fields are saved/deleted appropriately when toggling features
- Admin UI uses WordPress standard styles and components
