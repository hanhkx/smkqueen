import { mkdir, writeFile } from "node:fs/promises";
import { dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const root = resolve(dirname(fileURLToPath(import.meta.url)), "..");
const outputDir = resolve(root, "queen-alfalah-core", "assets", "images", "vacancies");

const logos = [
  {
    file: "mahkota-travel.png",
    url: "https://mahkotatravel.com/wp-content/uploads/2024/09/Logo-Mahkota-Hitam.png",
  },
  {
    file: "general-solusindo.jpg",
    url: "https://images.glints.com/unsafe/fit-in/600x600/glints-dashboard.oss-ap-southeast-1-internal.aliyuncs.com/company-logo/28959e7a6164bdeb7bd0a518516a52fd.jpg",
  },
  {
    file: "guna-coalindo.jpg",
    url: "https://media.licdn.com/dms/image/v2/D560BAQFDCru-8YfN6Q/company-logo_200_200/company-logo_200_200/0/1734596774261?e=2147483647&v=beta&t=6Z8UDdkhSlYkJ58U4li6yPvsEPI62fvAIfaH14-EjOs",
  },
  {
    file: "kediri-service-center.png",
    url: "https://images.glints.com/unsafe/fit-in/600x600/glints-dashboard.oss-ap-southeast-1-internal.aliyuncs.com/company-logo/b101cf71448821d2647a28acb7dfbc41.png",
  },
  {
    file: "caretaker-id.jpg",
    url: "https://media.licdn.com/dms/image/v2/D560BAQECbb1cWT9aGA/company-logo_200_200/B56aA.FVppJ4AE-/0/1787747982229?e=2147483647&v=beta&t=k6jxtBCoCy6VyUb_s_RGAqpbciPgy1gCYzAOJYDlTEQ",
  },
  {
    file: "wins-dental-care.jpg",
    url: "https://scontent-cgk2-1.cdninstagram.com/v/t51.2885-19/13534518_1728148274091754_1496498652_a.jpg?stp=dst-jpg_s100x100_tt6&_nc_cat=106&ccb=7-5&_nc_sid=bf7eb4&efg=eyJ2ZW5jb2RlX3RhZyI6InByb2ZpbGVfcGljLnd3dy45NjAuQzMifQ%3D%3D&_nc_ohc=4nCkNoGoUqUQ7kNvwExixhv&_nc_oc=AdqGllkqYwyf5BwlDuZ6XR3oe8BADOjm9LLtnhKGMqGo3uvbJ0feiE5gGPSgZC0m4No&_nc_zt=24&_nc_ht=scontent-cgk2-1.cdninstagram.com&_nc_ss=7da8c&oh=00_AQJ3HQ5ychZYezeoOl7aV5qPDE7u_3QMLWxEGskn1sXLHg&oe=6AB063D2",
  },
];

await mkdir(outputDir, { recursive: true });

for (const logo of logos) {
  const response = await fetch(logo.url, {
    headers: { "user-agent": "Mozilla/5.0 (SMK Queen Al-Falah vacancy asset sync)" },
  });
  if (!response.ok) {
    throw new Error(`${logo.file}: HTTP ${response.status}`);
  }
  const contentType = response.headers.get("content-type") || "";
  if (!contentType.startsWith("image/")) {
    throw new Error(`${logo.file}: unexpected content type ${contentType}`);
  }
  const bytes = new Uint8Array(await response.arrayBuffer());
  if (bytes.length < 512) {
    throw new Error(`${logo.file}: image response is unexpectedly small`);
  }
  await writeFile(resolve(outputDir, logo.file), bytes);
  process.stdout.write(`${logo.file}\t${bytes.length}\t${contentType}\n`);
}
