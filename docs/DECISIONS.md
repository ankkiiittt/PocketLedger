# Architectural & Implementation Decisions

Record of technical and product decisions, conflicts resolved, and additions made during the build of PocketLedger.

## Decision Log

### D-001: Toolchain and Runtime Environment
- **Context**: The host machine did not have PHP 8.3 in global PATH.
- **Decision**: Configured official portable PHP 8.3 (x64) with extensions: `mbstring`, `intl`, `bcmath`, `pdo_mysql`, `xml`, `curl`, `zip`, `fileinfo`, `openssl`, and installed Composer 2.
- **Status**: Accepted.

### D-002: MySQL 8.0 Databases
- **Context**: Local MySQL 8.0 service `MYSQL80` is running on port 3306.
- **Decision**: Created `pocketledger` and `pocketledger_test` databases with `utf8mb4` charset and `utf8mb4_unicode_ci` collation. Configured credentials in `.env` and `phpunit.xml`.
- **Status**: Accepted.

### D-003: Front-end Tooling & Strict TypeScript
- **Context**: Removed default Tailwind and Axios from Laravel skeleton as required by TRD section 2.
- **Decision**: Installed `typescript`, `vitest`, `jsdom`, `eslint`, `@eslint/js`, `typescript-eslint`, `chart.js`, and self-hosted fonts `@fontsource-variable/inter` and `@fontsource-variable/fraunces`. Configured `tsconfig.json`, `vite.config.ts`, `vitest.config.ts`, and `eslint.config.js`.
- **Status**: Accepted.

### D-004: Pest and Larastan
- **Context**: Testing with Pest and static analysis with Larastan level 6.
- **Decision**: Configured `tests/Pest.php`, `phpunit.xml`, and `phpstan.neon` at level 6.
- **Status**: Accepted.
