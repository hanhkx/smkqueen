const fs = require("fs");
const path = require("path");

const parserRoot = process.argv[2];
if (!parserRoot) {
  throw new Error("Pass the php-parser directory as the first argument.");
}

const Engine = require(path.join(parserRoot, "src", "index.js"));
const engine = new Engine({
  parser: { extractDoc: true, php7: true },
  ast: { withPositions: true },
});

const roots = ["queen-alfalah", "queen-alfalah-core", "tests"];
const files = [];
for (const root of roots) {
  const walk = (directory) => {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
      const file = path.join(directory, entry.name);
      if (entry.isDirectory()) walk(file);
      else if (entry.isFile() && file.endsWith(".php")) files.push(file);
    }
  };
  walk(path.resolve(root));
}

const failures = [];
for (const file of files) {
  try {
    engine.parseCode(fs.readFileSync(file, "utf8"), file);
  } catch (error) {
    failures.push(`${file}: ${error.message}`);
  }
}

if (failures.length) {
  process.stderr.write(failures.join("\n") + "\n");
  process.exit(1);
}
process.stdout.write(`PASS: parsed ${files.length} PHP files.\n`);
