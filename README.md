# Packager

Run Composer commands within a PHP application. This library acts as a PHP wrapper for Composer commands, allowing package management through PHP.

You technically *do not* need Composer to be installed, as this library will bring in the Composer application as a dependency - however, you will need Composer to install this library itself.

## Security Implications

Note that this library does introduce some security implications which you may need to consider before using.

- If this library is used in a web-based environment, you must make the vendor folder and files writable for the user running your web server. You **should not** make your vendor folder web-accessible, otherwise this will become an easy vector for an attack.
- Allowing scripts to run within Composer may grant the script access to your web environment or outlying PHP application. You should not run scripts unless you absolutely trust your dependencies.
