# Payroll Manager - Setup & Troubleshooting Guide

## 🚀 Quick Setup

### 1. **Database Setup**
- Go to your Hostinger MySQL Database panel (cPanel)
- Create a new database named: `u768023141_u123_payroll`
- Create a user: `u768023141_u123_admin` with password: `Raja#184`
- Grant all privileges to this user for the database
- Run the SQL from `database_setup.sql` in the SQL editor

### 2. **Test Database Connection**
```bash
# Start PHP server in the payroll folder
cd /Users/saurabhpandey/Downloads/payrollmanager\ perfect\ \(1\)
php -S localhost:8000
```

Then open in browser:
```
http://localhost:8000/test_connection.php
```

You should see:
- ✅ Connected Successfully
- ✅ employees table EXISTS
- ✅ history table EXISTS
- ✅ submissions table EXISTS
- ✅ settings table EXISTS

### 3. **Access the Application**
```
http://localhost:8000/index.html
```

## 🔍 How to Check Errors

### Browser Console Errors
1. Open DevTools: **F12 or Cmd+Option+I**
2. Go to **Console** tab
3. Look for red error messages
4. Common errors:
   - `Cannot POST /api.php` → File not found
   - `Database error` → Connection issue
   - `Network error` → Server not running

### API Response Errors
The application now logs all API errors. Check the Console for messages like:
```
API Error: [error message]
```

### Test API Directly
```bash
# Test getting all employees
curl -X GET "http://localhost:8000/api.php?action=get_all"

# Test adding employee (Linux/Mac)
curl -X POST "http://localhost:8000/api.php?action=save_employee" \
  -H "Content-Type: application/json" \
  -d '{"id":999,"name":"Test Employee","cardNo":"TEST001","salary":20000}'
```

## 📋 Import CSV Format

Your CSV file should have columns in this exact order:
```
name, cardNo, salary, wages, bonus, hra, ot, misc, days, hrs, pf, esic, gwlf, pt, advance, food, trn, rr, leave_amt, uan, esicNo, agt, ifscCode, accountNumber, company
```

**Example:**
```csv
name,cardNo,salary,wages,bonus,hra,ot,misc,days,hrs,pf,esic,gwlf,pt,advance,food,trn,rr,leave_amt,uan,esicNo,agt,ifscCode,accountNumber,company
John Doe,EMP001,25000,0,2000,5000,1000,500,26,208,2000,1500,0,1000,0,500,0,0,0,UAN123,ESIC456,Agent1,SBIN123,1234567890,SOHONI METAL CRAFT
Jane Smith,EMP002,20000,5000,1000,3000,500,300,24,192,1500,1000,0,800,2000,300,0,0,0,UAN124,ESIC457,Agent2,HDFC456,9876543210,LED X
```

## 🛠️ Troubleshooting

### Problem: "Import not working"
**Solution:**
1. Check browser console (F12) for errors
2. Verify CSV format matches the template above
3. Ensure each row has data in required fields (name, salary, etc.)
4. Run `test_connection.php` to verify database

### Problem: "New employee not saving"
**Solution:**
1. Fill in at least: Name and one numeric field (Salary)
2. Check console for "API Error" messages
3. Verify database connection with `test_connection.php`
4. Check that `id` field is being generated (it should be automatic)

### Problem: "Edit not updating"
**Solution:**
1. Make sure you're editing an existing employee
2. Change at least one field
3. Click Save
4. Check console for errors

### Problem: "API returning errors"
**Check server logs:**
```bash
# The API now shows detailed errors - check console output
# If using Hostinger, check Error Logs in cPanel
```

## 📝 Database Tables Schema

### employees
- `id`: BIGINT (primary key)
- `name`: VARCHAR - Employee name
- `cardNo`: VARCHAR - ID card number
- `salary`: INT - Basic salary
- `wages`: INT - Additional wages
- `bonus`, `hra`, `ot`, `misc`: INT - Various income components
- `days`, `hrs`: INT - Days worked, hours worked
- `pf`, `esic`, `pt`, `advance`, `food`: INT - Deduction components
- `accountNumber`, `ifscCode`: VARCHAR - Bank details
- `company`: VARCHAR - Company/Site name

### history
- `id`: BIGINT AUTO_INCREMENT
- `name`: VARCHAR - Batch name
- `date`: VARCHAR - Batch date
- `data`: LONGTEXT - JSON encoded payroll data

### submissions
- `id`: BIGINT AUTO_INCREMENT
- `companyName`: VARCHAR
- `date`: VARCHAR
- `data`: LONGTEXT - JSON encoded form data

### settings
- `id`: INT (default 1)
- `config`: LONGTEXT - JSON configuration

## 💡 What the Fixes Include

### ✅ PHP API Improvements
- Better error messages with HTTP status codes
- Detailed error logging
- Validation of required fields
- Support for INSERT/UPDATE operations
- CORS headers for local testing
- OPTIONS request handling

### ✅ HTML/JavaScript Improvements
- Proper error handling from API responses
- Fallback to demo mode if API fails
- Better import with async/await
- Real-time state updates
- Comprehensive error logging to console

### ✅ Data Flow
1. User enters data → Form validation
2. Form submitted → handleSaveEmployee()
3. API called → api() function
4. PHP receives → Validates and saves
5. Response returned → State updated immediately
6. UI refreshed → New data visible

## 🎯 Key Features Now Working

✅ **Import CSV** - Properly awaits each save, shows success count
✅ **Add Employee** - Creates new employee with auto-generated ID
✅ **Edit Employee** - Updates existing employee fields
✅ **View Payslips** - Shows professional payslip design
✅ **Print Payslips** - One slip per page, proper formatting
✅ **API Integration** - Full CRUD operations to database

## ⚠️ Important Notes

1. **File Protocol Won't Work** - Must use HTTP (localhost:8000), not file://
2. **Database Required** - Demo mode is fallback only
3. **Credentials** - Are visible in api.php (fine for local dev, change in production)
4. **CORS** - Enabled for all origins in api.php

## 📞 Getting Help

If you still have issues:
1. Run `test_connection.php` to verify database
2. Check browser console (F12) for error messages
3. Check PHP error logs in Hostinger cPanel
4. Verify CSV file format
5. Make sure you're using HTTP (localhost:8000), not file://
