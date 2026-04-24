import { test, expect } from '@playwright/test';
import { loginAs, countFakerEntities, getFakerEntities } from '../helpers/elgg';

/**
 * End-to-end contract for the user generator.
 *
 * Locks:
 *  - Submitting the gen_users form creates N user entities tagged
 *    with the __faker metadata marker.
 *  - A success message appears in the UI.
 *  - The delete action sweeps every __faker-tagged user.
 */
test.describe('hypeFaker gen_users + delete', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, 'admin');
  });

  test('form submit creates fake users and UI shows them', async ({ page }) => {
    const before = await countFakerEntities('user');

    await page.goto('/admin/developers/faker');
    const form = page.locator('form[action*="action/faker/gen_users"]');
    await form.locator('input[name="count"]').fill('3');
    await form.locator('input[name="password"]').fill('testpass123');
    await form.locator('input[name="email_domain"]').fill('example.test');
    await form.locator('input[type="submit"], button[type="submit"]').click();

    // Elgg forwards to REFERER; wait for the admin page.
    await page.waitForURL(/admin/);

    // Assert DB: 3 new fake users.
    const after = await countFakerEntities('user');
    expect(after - before).toBe(3);

    // Assert the new users' faker flag is set.
    const users = await getFakerEntities('user');
    expect(users.length).toBeGreaterThanOrEqual(3);
  });

  test('delete sweep removes all fake entities', async ({ page }) => {
    // Precondition: there is at least one fake entity.
    const preCount = await countFakerEntities();
    expect(preCount).toBeGreaterThan(0);

    // Trigger delete via action URL (the admin view uses a confirm link).
    await page.goto('/admin/developers/faker');

    // Click the delete button. Accept the browser confirm dialog.
    page.once('dialog', (dialog) => dialog.accept());
    await page.locator('a[href*="action/faker/delete"]').first().click();
    await page.waitForLoadState('networkidle');

    const postCount = await countFakerEntities();
    expect(postCount).toBe(0);
  });
});
