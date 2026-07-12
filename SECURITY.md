# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within Subbase, please send an email to [Nafis Watsiq](mailto:nafiswatsiq@gmail.com). All security vulnerabilities will be promptly addressed.

Please do **not** report security vulnerabilities through public GitHub issues.

### What to include

When reporting a vulnerability, please include:

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Any suggested fix (if applicable)

### Response timeline

- **Acknowledgment**: We will acknowledge receipt of your vulnerability report within **48 hours**.
- **Assessment**: We will assess the severity and validity of the report within **5 business days**.
- **Fix**: We aim to provide a fix for confirmed vulnerabilities within **30 days** of confirmation, depending on complexity.

### Disclosure policy

- We request that you give us reasonable time to address the issue before public disclosure.
- We will credit reporters who follow responsible disclosure practices, unless they prefer to remain anonymous.

## Security Best Practices for Users

When using Subbase in production, we recommend:

- Keep the package updated to the latest version
- Review all discount and subscription configurations before deployment
- Use proper authorization checks when exposing subscription management to end users
- Validate discount codes server-side before applying them
- Monitor usage of discount codes for abuse patterns
