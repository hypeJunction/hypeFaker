import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(
  page: Page,
  username: string,
  password: string = 'testpass123'
) {
  await page.goto('/login');
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await page.click('input[type="submit"], button[type="submit"]');
  await page.waitForLoadState('networkidle');
}

export async function queryDb(sql: string, params: any[] = []) {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

/**
 * Count entities marked with the hypeFaker __faker metadata flag.
 * Mirrors `elgg_get_entities(['metadata_names' => '__faker', 'count' => true])`.
 */
export async function countFakerEntities(type?: string): Promise<number> {
  let sql = `
    SELECT COUNT(DISTINCT e.guid) AS c
    FROM elgg_entities e
    INNER JOIN elgg_metadata m ON m.entity_guid = e.guid
    WHERE m.name = '__faker'
  `;
  const params: any[] = [];
  if (type) {
    sql += ' AND e.type = ?';
    params.push(type);
  }
  const rows = await queryDb(sql, params);
  return Number(rows[0]?.c ?? 0);
}

export async function getFakerEntities(type?: string): Promise<any[]> {
  let sql = `
    SELECT DISTINCT e.*
    FROM elgg_entities e
    INNER JOIN elgg_metadata m ON m.entity_guid = e.guid
    WHERE m.name = '__faker'
  `;
  const params: any[] = [];
  if (type) {
    sql += ' AND e.type = ?';
    params.push(type);
  }
  return queryDb(sql, params);
}
