---
paths:
  - 'app/Http/Controllers/**/*Controller.php'
---

# Controllers

## Sanitize spreadsheet exports
All CSV exports must pass rows through SpreadsheetValueSanitizer::sanitizeCsvRow(). XLSX exports containing user-managed data must call SpreadsheetValueSanitizer::bindStrings() before writing cells so formulas are stored as text.
