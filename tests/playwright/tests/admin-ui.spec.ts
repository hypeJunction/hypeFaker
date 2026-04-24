import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

/**
 * Admin UI smoke tests for hypeFaker.
 *
 * Lock:
 *  - admin can reach /admin/developers/faker
 *  - the user-generator form renders (first form is always available)
 *  - the page menu entry "faker" appears in admin develop section
 */
test.describe('hypeFaker admin UI', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'admin');
  });

  test('admin develop menu lists faker entry', async ({ page }) => {
    await page.goto('/admin');
    const fakerLink = page.locator('a[href*="admin/developers/faker"]').first();
    await expect(fakerLink).toBeVisible();
  });

  test('developer faker page renders without PHP errors', async ({ page }) => {
    const response = await page.goto('/admin/developers/faker');
    expect(response?.status()).toBeLessThan(400);

    // No system error messages.
    await expect(
      page.locator('.elgg-system-messages .elgg-message-error')
    ).toHaveCount(0);

    // Page should contain an h1/title for the faker admin area.
    await expect(page.locator('#faker-log')).toBeVisible();
  });

  test('user generator form is present', async ({ page }) => {
    await page.goto('/admin/developers/faker');
    await expect(
      page.locator('form[action*="action/faker/gen_users"]')
    ).toBeVisible();
    await expect(page.locator('input[name="count"]').first()).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="email_domain"]')).toBeVisible();
  });

  test('non-admin cannot access faker page', async ({ page, context }) => {
    await context.clearCookies();
    // Try to hit the admin page unauthenticated -> Elgg redirects to login.
    const response = await page.goto('/admin/developers/faker');
    const url = page.url();
    expect(
      url.includes('/login') ||
        (response?.status() ?? 0) >= 300
    ).toBeTruthy();
  });
});
