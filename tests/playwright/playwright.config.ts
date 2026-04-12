import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  baseURL: process.env.ELGG_BASE_URL || 'http://elgg',
  timeout: 60000,
  use: {
    ignoreHTTPSErrors: true,
  },
  // hypeFaker mutates global site state (users, groups, content) — must
  // run sequentially to avoid race conditions.
  workers: 1,
  projects: [{ name: 'chromium', use: { browserName: 'chromium' } }],
});
