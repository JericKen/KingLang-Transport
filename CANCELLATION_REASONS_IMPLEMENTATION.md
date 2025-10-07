# Cancellation Reasons Implementation

## Overview
This implementation adds a structured cancellation reason selection system for clients when canceling bookings. Instead of a simple textarea, clients now select from predefined categories with an option for custom reasons.

## Features Added

### 1. Database Schema Updates
- **File**: `database/add_cancellation_reason_category.sql`
- **Changes**: Added `cancellation_reason_category` and `custom_reason` fields to `canceled_trips` table
- **Index**: Added index on `cancellation_reason_category` for better query performance

### 2. Configuration File
- **File**: `config/cancellation_reasons.php`
- **Purpose**: Centralized configuration for cancellation reasons and categories
- **Features**: 
  - 9 predefined reason categories
  - "Other (Please Specify)" option
  - Icons and descriptions for each reason
  - Categorized grouping for analytics

### 3. Frontend Updates
- **File**: `public/js/client/booking_request.js`
- **Changes**:
  - New `showCancellationReasonModal()` function
  - Updated `cancelBooking()` function to handle category parameter
  - Replaced textarea input with structured reason selection
  - Dynamic "Other" option with custom textarea
  - Form validation for required fields

### 4. Backend Updates

#### Controller Updates
- **File**: `app/controllers/client/BookingController.php`
- **Changes**: Updated `cancelBooking()` method to handle `reasonCategory` parameter

#### Model Updates
- **Files**: 
  - `app/models/client/BookingModel.php`
  - `app/models/admin/BookingManagementModel.php`
- **Changes**: Updated `cancelBooking()` methods to store both category and custom reason

## Cancellation Reason Categories

1. **Schedule Conflict** - Conflicting appointment or event
2. **Financial Issues** - Unable to afford the trip
3. **Family Emergency** - Unexpected family situation
4. **Health Concerns** - Health issues preventing travel
5. **Weather Concerns** - Concerned about weather conditions
6. **Found Alternative** - Found better option or service
7. **Group Size Changed** - Number of participants changed
8. **Destination Changed** - Decided to go to different destination
9. **Service Concerns** - Concerns about service quality
10. **Other (Please Specify)** - Custom reason with textarea

## User Experience

### Before
- Simple textarea for cancellation reason
- No structure or categorization
- Difficult to analyze cancellation patterns

### After
- Structured reason selection with icons and descriptions
- Predefined categories for easy analytics
- "Other" option for flexibility
- Better user experience with clear options

## Analytics Benefits

1. **Categorized Data**: Cancellation reasons are now categorized for better analysis
2. **Trend Analysis**: Easy to identify common cancellation patterns
3. **Service Improvement**: Identify areas for service improvement based on cancellation reasons
4. **Reporting**: Generate reports on cancellation reasons by category

## Database Structure

```sql
-- New fields in canceled_trips table
cancellation_reason_category VARCHAR(100) -- e.g., 'schedule_conflict', 'financial_issues'
custom_reason TEXT -- Only populated when category is 'other'
```

## Usage

1. Client clicks "Cancel" button on a booking
2. Modal opens with predefined reason options
3. Client selects appropriate reason or chooses "Other"
4. If "Other" is selected, custom textarea appears
5. Form validates that a reason is selected
6. Cancellation is processed with both category and reason stored

## Files Modified

1. `database/add_cancellation_reason_category.sql` - Database migration
2. `config/cancellation_reasons.php` - Configuration file
3. `public/js/client/booking_request.js` - Frontend JavaScript
4. `app/controllers/client/BookingController.php` - Backend controller
5. `app/models/client/BookingModel.php` - Client model
6. `app/models/admin/BookingManagementModel.php` - Admin model

## Testing

To test the implementation:
1. Run the database migration
2. Navigate to client booking requests page
3. Click "Cancel" on any pending booking
4. Verify the new reason selection modal appears
5. Test selecting different reasons
6. Test the "Other" option with custom text
7. Verify cancellation is processed correctly

## Future Enhancements

1. **Admin Analytics Dashboard**: Show cancellation reason statistics
2. **Reason Trends**: Track cancellation reason trends over time
3. **Automated Responses**: Send different responses based on cancellation reason
4. **Prevention Strategies**: Use data to prevent common cancellation reasons
