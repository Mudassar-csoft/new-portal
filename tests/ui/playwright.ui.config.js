import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './',
  timeout: 60000,
  retries: 0,
  use: {
    baseURL: process.env.UI_BASE_URL || 'http://127.0.0.1:8010',
    headless: true,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    channel: 'msedge',
    viewport: { width: 1440, height: 900 },
  },
  reporter: [['list']],
});
