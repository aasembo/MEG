# Document Upload and S3 Integration

This document outlines the new document upload functionality for the MEG case management system, including S3 integration and procedure assignment features.

## Features Added

### 1. AWS S3 Document Storage
- Secure document storage in AWS S3
- Organized folder structure: `Patient_{patient_id}_case_{case_id}/[procedure_{procedure_id}/]`
- Support for multiple file types: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, TXT
- File size limit: 50MB per file
- Server-side encryption (AES256)

### 2. Assign Procedures View
- Dedicated interface for managing case procedures
- Organized by modality for better navigation
- Visual feedback for current assignments
- Bulk add/remove procedure functionality
- Real-time selection summary

### 3. Document Upload Interface
- Modal-based upload interface
- Link documents to specific procedures (optional)
- Document type categorization
- Description field for additional context
- File validation and error handling

### 4. Document Management
- View all case documents in organized table
- Download documents with pre-signed URLs (1-hour expiration)
- File type icons and human-readable file sizes
- Integration with procedure tracking

## Technical Implementation

### Controllers
- **CasesController::assignProcedures()** - Dedicated procedure assignment
- **CasesController::uploadDocument()** - Document upload handling
- **CasesController::downloadDocument()** - Secure document download

### Services
- **S3DocumentService** - AWS S3 integration service
  - Reads AWS credentials directly from environment variables
  - Upload documents with metadata
  - Generate pre-signed download URLs
  - Delete documents
  - List documents by case/procedure

### Models
- Enhanced **Document** entity with utility methods
- Updated **CasesExamsProcedure** entity with document count methods
- Enhanced **DocumentsTable** with new field validation

### Templates
- **assign_procedures.php** - Dedicated procedure assignment interface
- Enhanced **view.php** with upload modals and document management

## Configuration

### AWS S3 Setup
The S3DocumentService reads AWS credentials **directly from environment variables** loaded from the `config/.env` file. Add the following to your `config/.env`:

```bash
AWS_ACCESS_KEY_ID="your-aws-access-key-id"
AWS_SECRET_ACCESS_KEY="your-aws-secret-access-key"
AWS_S3_REGION="us-east-1"
AWS_S3_BUCKET="meg-documents"
```

**Note**: The service bypasses CakePHP's configuration system and reads environment variables directly for better security and simplicity.

### Environment Variables Setup
1. Copy `config/.env.example` to `config/.env`
2. Update the AWS credentials in the `.env` file
3. Ensure the `.env` file is added to your `.gitignore` for security

### Security Note
**IMPORTANT**: Never commit the `.env` file to version control. It contains sensitive credentials.
```bash
AWS_ACCESS_KEY_ID=your-aws-access-key-id
AWS_SECRET_ACCESS_KEY=your-aws-secret-access-key
AWS_S3_REGION=us-east-1
AWS_S3_BUCKET=meg-documents
```

### Database Schema
The following columns were added to the `documents` table:
- `file_size` (BIGINT) - File size in bytes
- `original_filename` (VARCHAR 255) - Original uploaded filename

## Security Features

### File Validation
- MIME type validation
- File size restrictions
- Allowed file extensions only
- Upload file verification

### Access Control
- Hospital context verification
- Case ownership validation
- User authentication required
- Pre-signed URLs with expiration

### S3 Security
- Server-side encryption enabled
- Metadata tagging for organization
- Bucket access via IAM credentials only

## Usage Workflow

### For Technicians

1. **Case Creation/Editing**
   - Create case normally
   - Use "Assign Procedures" for bulk procedure management

2. **Procedure Assignment**
   - Click "Assign Procedures" from case view
   - Select/deselect procedures by modality
   - Visual feedback shows current selection
   - Save updates procedures for the case

3. **Document Upload**
   - Click "Upload Documents" from case view or procedure row
   - Select document type and file
   - Optionally link to specific procedure
   - Add description if needed
   - Upload stores in organized S3 structure

4. **Document Access**
   - View documents from case overview
   - Download generates secure temporary URL
   - Documents organized by type and procedure

## File Organization Structure

```
S3 Bucket: meg-documents/
├── Patient_123_case_456/
│   ├── general_documents/
│   │   ├── consent_2024-10-07_abc123.pdf
│   │   └── referral_2024-10-07_def456.doc
│   └── procedure_789/
│       ├── image_2024-10-07_ghi789.jpg
│       └── report_2024-10-07_jkl012.pdf
```

## Error Handling

### Upload Errors
- File size validation
- File type restrictions
- S3 connection issues
- Database save failures

### Download Errors
- Missing files
- Access permission issues
- Expired URLs
- Network connectivity

### User Feedback
- Success/error flash messages
- Real-time validation feedback
- Progress indicators for uploads
- Clear error descriptions

## Performance Considerations

### S3 Optimization
- Pre-signed URLs for direct browser downloads
- Metadata for efficient organization
- Regional bucket placement
- Connection pooling for uploads

### Database Efficiency
- Indexed foreign keys
- Optimized contain queries
- Lazy loading for large document lists

## Maintenance

### Regular Tasks
- Monitor S3 storage usage
- Clean up orphaned documents
- Audit access logs
- Update security policies

### Backup Strategy
- S3 versioning enabled
- Cross-region replication
- Database backup includes document metadata
- Regular restore testing

## Future Enhancements

### Planned Features
- Document versioning
- Bulk document upload
- Document previews
- Advanced search and filtering
- Document sharing between cases
- Automated document processing
- Integration with DICOM viewers

### Technical Improvements
- CDN integration for faster downloads
- Document compression for storage efficiency
- Thumbnail generation for images
- Full-text search capabilities
- Automated backup verification