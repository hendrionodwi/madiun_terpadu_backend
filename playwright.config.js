import { defineConfig } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8000';

export default defineConfig({
    testDir: './tests/browser',
    timeout: 120_000,
    outputDir: './test-results',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [
        ['list'],
        ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ],
    use: {
        baseURL,
        browserName: 'chromium',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        video: 'retain-on-failure',
    },
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: `${baseURL}/up`,
        reuseExistingServer: true,
        timeout: 120_000,
    },
    projects: [
        { name: 'mobile-360', use: { viewport: { width: 360, height: 800 } } },
        { name: 'mobile-390', use: { viewport: { width: 390, height: 844 } } },
        { name: 'tablet-768', use: { viewport: { width: 768, height: 1024 } } },
        { name: 'tablet-1024', use: { viewport: { width: 1024, height: 768 } } },
        { name: 'desktop-1366', use: { viewport: { width: 1366, height: 768 } } },
        { name: 'desktop-1440', use: { viewport: { width: 1440, height: 900 } } },
    ],
});
