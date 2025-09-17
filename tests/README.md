# McryptDev Testing Guide

## 🧪 Running Tests

### Prerequisites
```bash
# Install PHPUnit and dependencies
composer install
```

### Run All Tests
```bash
# Using composer script
composer test

# Or directly
./vendor/bin/phpunit
```

### Run Tests with Coverage
```bash
# Generate HTML coverage report
composer test-coverage

# View coverage report
open coverage/index.html
```

### Run Specific Tests
```bash
# Run specific test class
./vendor/bin/phpunit tests/Unit/McryptTest.php

# Run with testdox output (human readable)
./vendor/bin/phpunit --testdox

# Run specific method
./vendor/bin/phpunit --filter testEncryptDecrypt
```

## 📁 Test Structure

```
tests/
├── TestCase.php                              # Base test class
├── Unit/
│   ├── McryptTest.php                        # Core encryption tests
│   ├── KeyTest.php                          # Key management tests
│   └── Command/
│       ├── McryptAddEnvCommandTest.php      # Console command tests
│       └── McryptEncryptEnvCommandTest.php  # Console command tests
└── Fixtures/
    ├── test.env                             # Test environment file
    └── empty.env                            # Empty env file
```

## ✅ Test Coverage

Current test coverage includes:

### Core Functionality
- ✅ Encryption/Decryption operations
- ✅ Environment file management  
- ✅ Key loading and validation
- ✅ Error handling scenarios
- ✅ Edge cases and boundary conditions

### Console Commands
- ✅ McryptAddEnvCommand success/failure scenarios
- ✅ McryptEncryptEnvCommand functionality
- ✅ Command option and argument handling
- ✅ Error message validation

### Security Tests
- ✅ Double encryption prevention
- ✅ Invalid key handling
- ✅ Malformed data validation
- ✅ File permission scenarios

## 🔧 Test Configuration

The `phpunit.xml` configuration includes:
- Auto-discovery of test files
- Code coverage reporting
- Test environment setup
- Color output for better readability

## 💡 Writing New Tests

When adding new tests, follow these patterns:

1. **Extend TestCase** - Use the base TestCase class for shared setup
2. **Use Fixtures** - Store test data in the Fixtures directory
3. **Cleanup** - Always clean up temporary files in tests
4. **Assertions** - Use specific assertions for better error messages

Example:
```php
public function testNewFeature(): void
{
    // Arrange
    $testData = 'test input';
    
    // Act
    $result = $this->mcrypt->newMethod($testData);
    
    // Assert
    $this->assertSame('expected output', $result);
}
```

## 🐛 Debugging Tests

### View Test Output
```bash
# Verbose output
./vendor/bin/phpunit -v

# Show skipped and incomplete tests
./vendor/bin/phpunit --verbose
```

### Debug Specific Failures
```bash
# Stop on first failure
./vendor/bin/phpunit --stop-on-failure

# Show stack trace
./vendor/bin/phpunit --debug
```
