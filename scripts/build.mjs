import { cp, mkdir, readdir, rm } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const output = join(root, 'dist');

await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });

const rootFiles = await readdir(root, { withFileTypes: true });
for (const entry of rootFiles) {
    if (entry.isFile() && entry.name.endsWith('.html')) {
        await cp(join(root, entry.name), join(output, entry.name));
    }
}

for (const directory of ['admin', 'assets']) {
    await cp(join(root, directory), join(output, directory), { recursive: true });
}
