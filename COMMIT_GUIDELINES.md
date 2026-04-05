# Conventional Commit Guidelines

This project uses **Conventional Commits** for clear, semantic commit messages that enable automatic changelog generation and better project history.

## Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

## Type

Must be one of the following:

- **feat**: A new feature
- **fix**: A bug fix
- **refactor**: Code refactoring (no feature change)
- **style**: Code style changes (formatting, semicolons, etc)
- **docs**: Documentation-only changes
- **test**: Adding or updating tests
- **perf**: Performance improvements
- **ci**: CI/CD pipeline configuration
- **chore**: Dependency updates, tool configuration, non-src changes
- **build**: Build system or dependency changes

## Scope

Optional but recommended. Indicates which component or area of the codebase is affected:

- `books` - Book catalog features
- `issues` - Book issue/checkout workflows
- `members` - Member management and profiles
- `fines` - Charge/fine administration
- `reports` - Reporting and analytics
- `inventory` - Copy/inventory tracking
- `ui` - User interface and components
- `api` - API endpoints and controllers
- `db` - Database migrations and models
- `auth` - Authentication and authorization
- `settings` - System configuration
- `notifications` - Email and notifications

## Subject

- Use imperative mood ("add" not "added" or "adds")
- Don't capitalize the first letter
- Maximum 50 characters
- No period at the end
- Reference issues only in the footer, not in the subject

**Examples:**
```
feat(inventory): add accession number tracking for book copies
fix(issues): resolve race condition in copy reservation
docs(fines): update charge resolution workflow
```

## Body

Optional but recommended for clarity. Explain:

- **What** changed and **why** (not how—implementation details go in code)
- Problem being solved
- Context or background
- Side effects or impacts

- Maximum 72 characters per line
- Separate from subject with a blank line
- Can reference issues here: "Resolves #123" or "Fixes #456"

**Example:**
```
feat(inventory): add accession number tracking for book copies

Previously, books were tracked at title level only. This change
introduces per-copy tracking with unique accession numbers
to enable better inventory management and incident tracking.

Each copy now has:
- Unique accession_number (e.g., ACC001-001, ACC001-002)
- Status tracking (available, issued, lost, damaged)
- Copy-level circulation history

Resolves #45
```

## Footer

Optional. Used for:

- **References**: `References: #123, #456`
- **Closes**: `Closes: #789` (closes issue on merge)
- **Breaking-Change**: Document any breaking changes
- **Reviewed-by**: `Reviewed-by: @username`

**Example:**
```
Closes: #45
Reviewed-by: @librarian-user
```

## Complete Example

```
feat(fines): add waived status to charge resolution

Extend charge resolution workflow to support both paid and waived
states, allowing librarians to excuse fines when appropriate.

Changes:
- Add waived_at, resolved_by_user_id, resolution_notes to fines
- New /fines admin page with charge ledger and resolution modal
- Support filtering by charge status (unpaid, paid, waived)

Resolves #42
Closes: #78
```

## Setup Git to Use Template

Configure git to use the `.gitmessage` template automatically when committing:

```bash
# Set globally (all repositories)
git config --global commit.template ~/.gitmessage

# Set for this repository only
git config commit.template .gitmessage
```

After setup, `git commit` will open your editor with the template pre-filled.

## Validation (Optional)

To enforce conventional commits in CI/CD, you can:

1. **Use commitlint**: Pre-commit hook that validates commit messages
2. **Use husky**: Git hooks manager to automate checks
3. **GitHub Actions**: Validate commits in PR

Example with husky + commitlint:
```bash
npm install -D husky @commitlint/config-conventional @commitlint/cli
husky install
npx husky add .husky/commit-msg 'npx --no -- commitlint --edit "$1"'
```

## Benefits

✅ Clear project history  
✅ Automatic changelog generation  
✅ Semantic versioning (major.minor.patch)  
✅ Easy to search and filter commits  
✅ Automated CI/CD decisions  
✅ Better code review context  

## References

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Angular Commit Convention](https://github.com/angular/angular/blob/master/CONTRIBUTING.md#commit)
