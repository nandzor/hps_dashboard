# 🔐 Encryption Configuration Guide

## Overview
Sistem enkripsi CCTV Dashboard menggunakan `ENCRYPT_CREDENTIAL_KEY` yang terpisah dari `APP_KEY` untuk keamanan yang lebih baik.

## Environment Variables

### Required Encryption Settings
```env
# Encryption Settings
ENCRYPT_DEVICE_CREDENTIALS=true
ENCRYPT_STREAM_CREDENTIALS=true
ENCRYPTION_METHOD=AES-256-CBC
ENCRYPT_CREDENTIAL_KEY=your_32_character_base64_key_here
```

## Setup Instructions

### 1. Generate Credential Key
```bash
# Generate a new credential key
php artisan encrypt:generate-key

# Or manually generate:
# openssl rand -base64 32
```

### 2. Add to .env file
```env
ENCRYPT_CREDENTIAL_KEY=base64_generated_key_here
```

### 3. Clear Configuration Cache
```bash
php artisan config:clear
```

## Security Features

### ✅ Enhanced Security
- **Separate Key**: Credential encryption menggunakan key terpisah dari APP_KEY
- **Fallback Support**: Jika ENCRYPT_CREDENTIAL_KEY tidak ada, akan menggunakan APP_KEY
- **AES-256-CBC**: Menggunakan algoritma enkripsi yang kuat

### ✅ Encrypted Fields
- **DeviceMaster**: `username`, `password`
- **CctvStream**: `stream_username`, `stream_password`

### ✅ Environment Control
- `ENCRYPT_DEVICE_CREDENTIALS=true/false` - Enable/disable device credential encryption
- `ENCRYPT_STREAM_CREDENTIALS=true/false` - Enable/disable stream credential encryption

## Usage Examples

### Check Encryption Status
```php
use App\Helpers\EncryptionHelper;

$status = EncryptionHelper::getEncryptionStatus();
/*
Returns:
[
    'device_credentials_enabled' => true,
    'stream_credentials_enabled' => true,
    'encryption_method' => 'AES-256-CBC',
    'credential_key_set' => true,
    'app_key_set' => true
]
*/
```

### Manual Encryption/Decryption
```php
use App\Helpers\EncryptionHelper;

// Encrypt
$encrypted = EncryptionHelper::encrypt('sensitive_data');

// Decrypt
$decrypted = EncryptionHelper::decrypt($encrypted);
```

## Production Deployment

### Security Checklist
- [ ] Generate strong `ENCRYPT_CREDENTIAL_KEY`
- [ ] Set `ENCRYPT_DEVICE_CREDENTIALS=true`
- [ ] Set `ENCRYPT_STREAM_CREDENTIALS=true`
- [ ] Backup encryption keys securely
- [ ] Test encryption/decryption before deployment

### Key Management
- **Development**: Use different keys for each environment
- **Production**: Store keys securely (environment variables, key management service)
- **Backup**: Keep encrypted backups of keys

## Troubleshooting

### Common Issues
1. **Decryption fails**: Check if `ENCRYPT_CREDENTIAL_KEY` is correct
2. **Data not encrypted**: Verify `ENCRYPT_DEVICE_CREDENTIALS` is `true`
3. **Performance issues**: Consider disabling encryption for development

### Debug Commands
```bash
# Check encryption status
php artisan tinker
>>> App\Helpers\EncryptionHelper::getEncryptionStatus()

# Test encryption
>>> App\Helpers\EncryptionHelper::encrypt('test')
>>> App\Helpers\EncryptionHelper::decrypt('encrypted_value')
```
