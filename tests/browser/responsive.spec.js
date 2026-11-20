import { expect, test } from '@playwright/test';

const roleDashboards = [
    {
        name: 'admin DLH',
        email: 'admin.dlh@madiunkota.go.id',
        path: '/dashboard/admin',
    },
    {
        name: 'pengelola TPS3R',
        email: 'pengelola.tps3r@madiunkota.go.id',
        path: '/dashboard/tps3r',
    },
    {
        name: 'pemrakarsa',
        email: 'pemrakarsa@madiunkota.go.id',
        path: '/dashboard/pemrakarsa',
    },
];

async function expectNoDocumentOverflow(page) {
    const overflow = await page.evaluate(() => {
        const documentWidth = document.documentElement.scrollWidth;
        const viewportWidth = document.documentElement.clientWidth;

        return documentWidth - viewportWidth;
    });

    expect(overflow, 'halaman tidak boleh meluber secara horizontal').toBeLessThanOrEqual(1);
}

async function login(page, email) {
    await page.goto('/login', { waitUntil: 'commit' });
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('password');
    await page.locator('[data-test="login-button"]').click();
    await page.waitForURL(/\/dashboard(?:\/|$)/);
}

test('public pages remain readable without horizontal overflow', async ({ page }) => {
    for (const path of ['/', '/layanan', '/edukasi', '/marketplace', '/login', '/payment/return']) {
        await page.goto(path, { waitUntil: 'commit' });
        await expect(page.locator('body')).toBeVisible();
        await expectNoDocumentOverflow(page);
    }
});

test('home content slider keeps its height while changing slides', async ({ page }) => {
    await page.goto('/', { waitUntil: 'commit' });

    const viewport = page.locator('[data-test="home-content-slider-viewport"]');
    await expect(viewport).toBeVisible();

    const heightBefore = await viewport.evaluate((element) => element.getBoundingClientRect().height);
    expect(heightBefore).toBeGreaterThan(100);

    const nextButton = page.locator('[data-test="home-content-slider-next"]');
    if (await nextButton.isVisible()) {
        await nextButton.click();
        await page.waitForTimeout(600);

        const heightAfter = await viewport.evaluate((element) => element.getBoundingClientRect().height);
        expect(Math.abs(heightAfter - heightBefore)).toBeLessThanOrEqual(1);
    }
});

test('public header search and language controls are interactive', async ({ page }) => {
    test.skip(page.viewportSize().width < 1280, 'desktop header controls are visible from xl breakpoint');

    await page.goto('/', { waitUntil: 'commit' });
    await page.getByRole('button', { name: 'Buka pencarian portal' }).click();

    const searchDialog = page.getByRole('dialog', { name: 'Cari di portal' });
    await expect(searchDialog).toBeVisible();
    await searchDialog.locator('input[name="q"]').fill('TPS3R');
    await searchDialog.getByRole('button', { name: 'Cari', exact: true }).click();
    await page.waitForURL(/\/cari\?q=TPS3R/);
    await expect(page.getByRole('heading', { name: 'Pencarian portal' })).toBeVisible();

    await page.goto('/', { waitUntil: 'commit' });
    await page.getByTitle('Gunakan Bahasa Inggris').click();
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    await expect(page.getByRole('navigation', { name: 'Main navigation' })).toContainText('Schedules & Maps');
    await expect(page.getByRole('heading', { name: 'Together for a Clean and Sustainable Madiun' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Choose what you need' })).toBeVisible();

    await page.goto('/layanan', { waitUntil: 'commit' });
    await expect(page.getByRole('heading', { name: 'Service schedules & map' })).toBeVisible();

    await page.goto('/marketplace', { waitUntil: 'commit' });
    await expect(page.getByRole('heading', { name: 'Recycled Product Marketplace' })).toBeVisible();
});

test('dashboard date fields use the shared styled picker', async ({ page }) => {
    await login(page, 'admin.dlh@madiunkota.go.id');
    await page.goto('/dashboard/admin/broadcasts', { waitUntil: 'commit' });

    const dateInput = page.locator('input[name="published_at"]').first();
    await expect(dateInput).toHaveAttribute('data-civic-date-picker-ready', 'true');

    const trigger = dateInput.locator('xpath=..').locator('.civic-date-picker-trigger');
    await expect(trigger).toBeVisible();
    await trigger.click();

    const picker = page.locator('.civic-date-picker-panel');
    await expect(picker).toBeVisible();
    await expect(picker.locator('.civic-date-picker-weekdays')).toContainText('Sen');
    await picker.locator('[data-action="today"]').click();
    await picker.locator('[data-action="apply"]').click();

    await expect(dateInput).toHaveValue(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/);
    await expect(trigger).toHaveAttribute('aria-expanded', 'false');
    await expectNoDocumentOverflow(page);
});

test('image uploads show sizing recommendations', async ({ page }) => {
    await login(page, 'admin.dlh@madiunkota.go.id');
    await page.goto('/dashboard/admin/portal-settings', { waitUntil: 'commit' });

    const recommendations = page.locator('[data-image-upload-guidance]');
    await expect(recommendations).toHaveCount(2);
    await expect(recommendations.first()).toContainText('1920 × 1080 px');
    await expect(recommendations.last()).toContainText('1200 × 1200 px');
    await expectNoDocumentOverflow(page);
});

for (const role of roleDashboards) {
    test(`${role.name} dashboard remains responsive`, async ({ page }) => {
        await login(page, role.email);
        await page.goto(role.path, { waitUntil: 'commit' });

        await expect(page).toHaveURL(new RegExp(`${role.path}$`));
        await expect(page.locator('h1').first()).toBeAttached();
        await page.waitForTimeout(750);
        await expectNoDocumentOverflow(page);
    });
}
