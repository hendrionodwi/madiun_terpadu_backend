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
