import { test, expect } from '@playwright/test';

test.describe('UI smoke', () => {
  test('login page loads', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveTitle(/Career Institute/i);
    await expect(page.locator('h2')).toContainText('Login to your account');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await page.screenshot({ path: 'test-results/login-page.png', fullPage: true });
  });

  test('authenticated core pages render', async ({ page }) => {
    const pageErrors = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.goto('/login');
    await page.locator('input[name="email"]').fill('admin@example.com');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Login' }).click();

    await expect(page).toHaveURL(/\/$/);
    await page.screenshot({ path: 'test-results/dashboard-page.png', fullPage: true });

    const pages = [
      { path: '/', marker: /Dashboard|Leads|Admissions/i, screenshot: 'dashboard-authenticated.png' },
      { path: '/leads', marker: /Lead|All Leads/i, screenshot: 'leads-page.png' },
      { path: '/registration/status', marker: /Registration/i, screenshot: 'registration-status-page.png' },
      { path: '/admission/status', marker: /Admission/i, screenshot: 'admission-status-page.png' },
      { path: '/certificate', marker: /Certificate/i, screenshot: 'certificate-page.png' },
      { path: '/finance/dashboard', marker: /Finance|Income|Expense/i, screenshot: 'finance-dashboard-page.png' },
      { path: '/hrm/dashboard', marker: /HRM|Employees|Attendance|Payroll/i, screenshot: 'hrm-dashboard-page.png' },
      { path: '/inventory', marker: /Inventory/i, screenshot: 'inventory-page.png' },
    ];

    for (const target of pages) {
      await page.goto(target.path);
      await expect(page.locator('body')).toContainText(target.marker);
      await page.screenshot({ path: `test-results/${target.screenshot}`, fullPage: true });
    }

    expect(pageErrors, `Browser page errors: ${pageErrors.join('\n')}`).toEqual([]);
  });
});
