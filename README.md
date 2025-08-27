# Gutu Hospital Management System

This is a simple hospital management system for handling patient appointments.

## Database Setup

To use the 6-digit check-in code feature, you need to alter the `appointments` table in your database. Run the following SQL command:

```sql
ALTER TABLE appointments
ADD COLUMN checkin_code VARCHAR(6) NULL DEFAULT NULL AFTER qrCodeData;
```

This command adds a new column named `checkin_code` which will store the 6-digit code for patient check-in as an alternative to the QR code.
