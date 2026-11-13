from pathlib import Path

from PIL import Image, ImageFilter, ImageOps


ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "research" / "instagram-2026-11"
OUTPUTS = {
    "osis": ("DfBlV-6kSWN-01.jpg", "pemilu-raya-osis-2026.webp"),
    "ftki": ("DfBnLuFkXgH-02.jpg", "olimpiade-ftki-2026.webp"),
    "digital": ("DfBxFQcEZ9R-01.jpg", "digitalisasi-pembelajaran-2026.webp"),
    "kombel": ("DfHuBoKCSjy-01.jpg", "kombel-mgmps-pembelajaran-interaktif-2026.webp"),
    "health": ("DfbQ8MPCUt7-01.jpg", "pemeriksaan-kesehatan-puskesmas-mojo-2026.webp"),
    "tka": ("DfZCXUCCUR4-01.jpg", "tka-kelas-xii-2026.webp"),
    "essay": ("DfcL9QKicuU-01.jpg", "prestasi-esai-poster-gic-2026.webp"),
    "totebag": ("DfcRyYKCWW0-02.jpg", "totebag-painting-lembu-suro-kilisuci-2026.webp"),
    "badminton": ("DfgEdJwSm4A-01.jpg", "badminton-cup-hut-golkar-62-2026.webp"),
}


def square_card(source: Path, size: int = 1200) -> Image.Image:
    with Image.open(source) as opened:
        image = ImageOps.exif_transpose(opened).convert("RGB")

    background = ImageOps.fit(image, (size, size), method=Image.Resampling.LANCZOS)
    background = background.filter(ImageFilter.GaussianBlur(radius=28))
    background = Image.blend(background, Image.new("RGB", (size, size), (17, 71, 57)), 0.28)

    foreground = ImageOps.contain(image, (size - 96, size - 96), method=Image.Resampling.LANCZOS)
    x = (size - foreground.width) // 2
    y = (size - foreground.height) // 2
    background.paste(foreground, (x, y))
    return background


def finalist_collage(size: int = 1200) -> Image.Image:
    half = size // 2
    canvas = Image.new("RGB", (size, size), (17, 71, 57))

    for offset, filename in ((0, "Dfj3gSvicje-01.jpg"), (half, "Dfj3gSvicje-02.jpg")):
        with Image.open(SOURCE / filename) as opened:
            image = ImageOps.exif_transpose(opened).convert("RGB")
        background = ImageOps.fit(image, (half, size), method=Image.Resampling.LANCZOS)
        background = background.filter(ImageFilter.GaussianBlur(radius=24))
        background = Image.blend(background, Image.new("RGB", (half, size), (17, 71, 57)), 0.24)
        foreground = ImageOps.contain(image, (half - 24, size - 32), method=Image.Resampling.LANCZOS)
        x = offset + (half - foreground.width) // 2
        y = (size - foreground.height) // 2
        canvas.paste(background, (offset, 0))
        canvas.paste(foreground, (x, y))

    canvas.paste(Image.new("RGB", (6, size), (237, 181, 43)), (half - 3, 0))
    return canvas


def save_all() -> None:
    targets = {
        "news": ROOT / "queen-alfalah-core" / "assets" / "images" / "news",
        "gallery": ROOT / "queen-alfalah-core" / "assets" / "images" / "gallery",
    }
    achievement_target = ROOT / "queen-alfalah-core" / "assets" / "images" / "achievements"
    work_target = ROOT / "queen-alfalah-core" / "assets" / "images" / "student-works"

    for folder in [*targets.values(), achievement_target, work_target]:
        folder.mkdir(parents=True, exist_ok=True)

    cards = {key: square_card(SOURCE / source_name) for key, (source_name, _) in OUTPUTS.items()}
    cards["finalists"] = finalist_collage()
    names = {key: output_name for key, (_, output_name) in OUTPUTS.items()}
    names["finalists"] = "finalis-kompetensi-akademik-2026.webp"

    for key, card in cards.items():
        for folder in targets.values():
            card.save(folder / names[key], "WEBP", quality=84, method=6)

    for key in ("ftki", "essay", "totebag", "badminton", "finalists"):
        cards[key].save(achievement_target / names[key], "WEBP", quality=84, method=6)

    cards["totebag"].save(work_target / names["totebag"], "WEBP", quality=84, method=6)


if __name__ == "__main__":
    save_all()
