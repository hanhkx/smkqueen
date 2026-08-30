import { mkdir } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import process from 'node:process';
import sharp from 'sharp';

const [, , inputArgument, outputArgument] = process.argv;

if (!inputArgument || !outputArgument) {
  throw new Error('Usage: node optimize-fallback-image.mjs <input> <output.webp>');
}

const inputPath = resolve(inputArgument);
const outputPath = resolve(outputArgument);

await mkdir(dirname(outputPath), { recursive: true });

await sharp(inputPath)
  .resize(1200, 800, {
    fit: 'cover',
    position: 'centre',
  })
  .webp({
    quality: 84,
    effort: 6,
    smartSubsample: true,
  })
  .toFile(outputPath);

const metadata = await sharp(outputPath).metadata();
if (metadata.format !== 'webp' || metadata.width !== 1200 || metadata.height !== 800) {
  throw new Error(`Unexpected output: ${metadata.format} ${metadata.width}x${metadata.height}`);
}

process.stdout.write(`${outputPath} (${metadata.width}x${metadata.height})\n`);
