#!/usr/bin/env node

/**
 * Build consistently padded 3:2 cards from verified partner logos/profile
 * photos. Official source files are intentionally supplied outside Git; the
 * generated WebP cards are the assets bundled with the WordPress plugin.
 *
 * Usage:
 *   node tools/build-partner-images.mjs <source-directory> <output-directory>
 */

import { createRequire } from 'node:module';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const require = createRequire(import.meta.url);
const sharp = require('sharp');

const sourceDirectory = process.argv[2];
const outputDirectory = process.argv[3];

if (!sourceDirectory || !outputDirectory) {
	throw new Error('Source and output directories are required.');
}

const width = 1200;
const height = 800;

const verified = [
	{ output: 'jtv-kediri.webp', source: 'jtv-kediri.png', name: 'JTV Kediri', kind: 'logo' },
	{ output: 'ourweb.webp', source: 'ourweb.webp', name: 'OurWeb.id', kind: 'logo' },
	{ output: 'terra-computer-kediri.webp', source: 'terra-computer.jpg', name: 'Terra Computer System Kediri', kind: 'profile' },
	{ output: 'cv-nusantara-media-mandiri.webp', source: 'cv-nusantara-media-mandiri.png', name: 'CV Nusantara Media Mandiri', kind: 'logo-square' },
	{ output: 'cv-besar-anugrah-djaya.webp', source: 'bead-group.png', name: 'BeAD IT Consultant', kind: 'logo-square' },
	{ output: 'candradimuka-digital.webp', source: 'candradimuka-digital.png', name: 'Candradimuka Digital', kind: 'logo' },
	{ output: 'lp3i-college-kediri.webp', source: 'lp3i.svg', name: 'LP3I College Kediri', kind: 'logo-white' },
	{ output: 'rs-bhayangkara-kediri.webp', source: 'rs-bhayangkara-kediri.png', name: 'RS Bhayangkara Tk. II Kediri', kind: 'logo' },
	{ output: 'uptd-puskesmas-mojo.webp', source: 'puskesmas-mojo.jpg', name: 'UPTD Puskesmas Mojo', kind: 'profile' },
	{ output: 'rsu-arga-husada.webp', source: 'rsu-arga-husada.png', name: 'Rumah Sakit Umum Arga Husada', kind: 'logo' },
];

const temporary = [
	{ output: 'fa-cinema.webp', initials: 'FA', name: 'FA Cinema' },
	{ output: 'pt-alfiz.webp', initials: 'PA', name: 'PT Alfiz' },
	{ output: 'beneficia-tech.webp', initials: 'BT', name: 'Beneficia Tech' },
	{ output: 'pt-jwb.webp', initials: 'JWB', name: 'PT JWB' },
	{ output: 'asterix-comp.webp', initials: 'AC', name: 'Asterix Comp' },
];

function escapeXml(value) {
	return String(value)
		.replaceAll('&', '&amp;')
		.replaceAll('<', '&lt;')
		.replaceAll('>', '&gt;')
		.replaceAll('"', '&quot;')
		.replaceAll("'", '&apos;');
}

function backgroundSvg() {
	return Buffer.from(`
		<svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
			<defs>
				<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
					<stop offset="0" stop-color="#ffffff"/>
					<stop offset="1" stop-color="#eef7f3"/>
				</linearGradient>
			</defs>
			<rect width="1200" height="800" fill="url(#bg)"/>
			<circle cx="1135" cy="55" r="150" fill="#0e6655" opacity="0.06"/>
			<circle cx="1080" cy="12" r="84" fill="none" stroke="#d2a122" stroke-width="18" opacity="0.12"/>
			<path d="M0 760 C260 700 420 820 690 754 C880 708 1030 724 1200 690 L1200 800 L0 800Z" fill="#0e6655" opacity="0.045"/>
		</svg>
	`);
}

function footerSvg(name, temporaryIdentity = false) {
	const safeName = escapeXml(name);
	const size = name.length > 30 ? 38 : name.length > 22 ? 43 : 48;
	const badge = temporaryIdentity ? 'IDENTITAS SEMENTARA' : 'MITRA DUDI';
	const badgeFill = temporaryIdentity ? '#9a5b14' : '#0e6655';
	return Buffer.from(`
		<svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
			<rect x="72" y="655" width="${temporaryIdentity ? 242 : 150}" height="38" rx="19" fill="${badgeFill}"/>
			<text x="${temporaryIdentity ? 193 : 147}" y="681" text-anchor="middle" fill="#ffffff" font-family="Segoe UI, Arial, sans-serif" font-size="17" font-weight="700" letter-spacing="1.5">${badge}</text>
			<text x="72" y="748" fill="#083f35" font-family="Segoe UI, Arial, sans-serif" font-size="${size}" font-weight="750">${safeName}</text>
		</svg>
	`);
}

async function makeLogoCard(item) {
	const input = path.join(sourceDirectory, item.source);
	const maxWidth = 'logo-square' === item.kind ? 420 : 'logo-white' === item.kind ? 280 : 980;
	const maxHeight = 'logo-square' === item.kind ? 420 : 'logo-white' === item.kind ? 310 : 390;
	const logo = await sharp(input, { density: 320 })
		.trim({ background: { r: 255, g: 255, b: 255, alpha: 0 } })
		.resize({ width: maxWidth, height: maxHeight, fit: 'inside', withoutEnlargement: false })
		.png()
		.toBuffer();
	const metadata = await sharp(logo).metadata();
	const left = Math.round((width - metadata.width) / 2);
	const top = Math.round(90 + (470 - metadata.height) / 2);
	const tile = Buffer.from('<svg width="390" height="390" xmlns="http://www.w3.org/2000/svg"><rect x="8" y="8" width="374" height="374" rx="90" fill="#0e6655"/><circle cx="322" cy="70" r="48" fill="#d2a122" opacity="0.92"/></svg>');
	const layers = [];
	if ('logo-white' === item.kind) {
		layers.push({ input: tile, left: 405, top: 85 });
	}
	layers.push(
		{ input: logo, left, top },
		{ input: footerSvg(item.name), left: 0, top: 0 },
	);

	await sharp(backgroundSvg())
		.composite(layers)
		.webp({ quality: 92, effort: 6 })
		.toFile(path.join(outputDirectory, item.output));
}

async function makeProfileCard(item) {
	const input = path.join(sourceDirectory, item.source);
	const photoSize = 430;
	const photo = await sharp(input)
		.resize(photoSize, photoSize, { fit: 'cover' })
		.sharpen({ sigma: 0.7 })
		.composite([
			{
				input: Buffer.from(`<svg width="${photoSize}" height="${photoSize}" xmlns="http://www.w3.org/2000/svg"><rect width="${photoSize}" height="${photoSize}" rx="46" fill="#fff"/></svg>`),
				blend: 'dest-in',
			},
		])
		.png()
		.toBuffer();
	const frame = Buffer.from(`<svg width="466" height="466" xmlns="http://www.w3.org/2000/svg"><rect x="7" y="7" width="452" height="452" rx="54" fill="none" stroke="#0e6655" stroke-width="14" opacity="0.15"/></svg>`);

	await sharp(backgroundSvg())
		.composite([
			{ input: frame, left: 367, top: 75 },
			{ input: photo, left: 385, top: 93 },
			{ input: footerSvg(item.name), left: 0, top: 0 },
		])
		.webp({ quality: 92, effort: 6 })
		.toFile(path.join(outputDirectory, item.output));
}

async function makeTemporaryCard(item) {
	const initials = escapeXml(item.initials);
	const monogramSize = item.initials.length > 2 ? 118 : 142;
	const body = Buffer.from(`
		<svg width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">
			<rect x="420" y="92" width="360" height="360" rx="92" fill="#0e6655"/>
			<circle cx="720" cy="142" r="62" fill="#d2a122" opacity="0.92"/>
			<text x="600" y="330" text-anchor="middle" fill="#ffffff" font-family="Segoe UI, Arial, sans-serif" font-size="${monogramSize}" font-weight="800">${initials}</text>
			<text x="600" y="520" text-anchor="middle" fill="#74501a" font-family="Segoe UI, Arial, sans-serif" font-size="27" font-weight="700">LOGO RESMI BELUM TERVERIFIKASI</text>
		</svg>
	`);

	await sharp(backgroundSvg())
		.composite([
			{ input: body, left: 0, top: 0 },
			{ input: footerSvg(item.name, true), left: 0, top: 0 },
		])
		.webp({ quality: 92, effort: 6 })
		.toFile(path.join(outputDirectory, item.output));
}

await mkdir(outputDirectory, { recursive: true });

for (const item of verified) {
	if ('profile' === item.kind) {
		await makeProfileCard(item);
	} else {
		await makeLogoCard(item);
	}
}

for (const item of temporary) {
	await makeTemporaryCard(item);
}

console.log(`Built ${verified.length + temporary.length} partner cards in ${outputDirectory}`);
