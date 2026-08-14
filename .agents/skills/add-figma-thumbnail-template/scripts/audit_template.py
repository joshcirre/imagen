#!/usr/bin/env python3
"""Audit one Imagen Figma thumbnail template's static integration contract."""

from __future__ import annotations

import argparse
import re
import struct
import sys
from pathlib import Path


PNG_SIGNATURE = b"\x89PNG\r\n\x1a\n"
TEMPORARY_FIGMA_ASSET = "figma.com/api/mcp/asset"


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Check an Imagen thumbnail recipe, picker, CSS hook, preview, and permanent assets.",
    )
    parser.add_argument("--root", type=Path, default=Path.cwd(), help="Imagen repository root (default: current directory).")
    parser.add_argument("--slug", required=True, help="Template slug used by thumbnailTemplates.")
    parser.add_argument("--node-id", required=True, help="Normalized Figma node ID, for example 5:9008.")
    parser.add_argument("--asset", action="append", default=[], help="Required filename under public/img/youtube-templates; repeat as needed.")
    return parser.parse_args()


def png_dimensions(path: Path) -> tuple[int, int] | None:
    try:
        with path.open("rb") as image:
            if image.read(8) != PNG_SIGNATURE:
                return None

            chunk_length = struct.unpack(">I", image.read(4))[0]
            chunk_type = image.read(4)

            if chunk_type != b"IHDR" or chunk_length < 8:
                return None

            return struct.unpack(">II", image.read(8))
    except (OSError, struct.error):
        return None


def read_text(path: Path, failures: list[str]) -> str:
    try:
        return path.read_text(encoding="utf-8")
    except OSError as error:
        failures.append(f"Cannot read {path}: {error}")
        return ""


def recipe_block(source: str, slug: str) -> str | None:
    start_pattern = re.compile(rf"^\s*'{re.escape(slug)}':\s*\{{", re.MULTILINE)
    start_match = start_pattern.search(source)

    if start_match is None:
        return None

    next_recipe = re.compile(r"^\s*'[a-z0-9-]+':\s*\{", re.MULTILINE).search(source, start_match.end())
    end = next_recipe.start() if next_recipe is not None else source.find("\n};", start_match.end())

    return source[start_match.start() : end if end >= 0 else len(source)]


def picker_block(source: str, slug: str) -> str | None:
    marker = f'data-template="{slug}"'
    marker_position = source.find(marker)

    if marker_position < 0:
        return None

    button_start = source.rfind("<button", 0, marker_position)
    button_end = source.find("</button>", marker_position)

    if button_start < 0 or button_end < 0:
        return None

    return source[button_start : button_end + len("</button>")]


def find_temporary_urls(root: Path) -> list[Path]:
    search_roots = [root / "resources", root / "public" / "img" / "youtube-templates"]
    text_suffixes = {".css", ".html", ".js", ".json", ".php", ".svg", ".txt"}
    matches: list[Path] = []

    for search_root in search_roots:
        if not search_root.exists():
            continue

        for path in search_root.rglob("*"):
            if not path.is_file() or path.suffix.lower() not in text_suffixes:
                continue

            try:
                if TEMPORARY_FIGMA_ASSET in path.read_text(encoding="utf-8", errors="ignore"):
                    matches.append(path)
            except OSError:
                continue

    return matches


def main() -> int:
    arguments = parse_arguments()
    root = arguments.root.resolve()
    slug = arguments.slug
    node_id = arguments.node_id.replace("-", ":")
    failures: list[str] = []

    if re.fullmatch(r"[a-z0-9]+(?:-[a-z0-9]+)*", slug) is None:
        failures.append(f"Invalid slug '{slug}'; use lowercase hyphen-case.")

    if re.fullmatch(r"\d+:\d+", node_id) is None:
        failures.append(f"Invalid Figma node ID '{arguments.node_id}'; expected digits:digits.")

    javascript_path = root / "resources" / "js" / "image-studio.js"
    blade_path = root / "resources" / "views" / "components" / "image-studio.blade.php"
    css_path = root / "resources" / "css" / "app.css"
    assets_path = root / "public" / "img" / "youtube-templates"

    javascript = read_text(javascript_path, failures)
    blade = read_text(blade_path, failures)
    css = read_text(css_path, failures)

    recipe = recipe_block(javascript, slug)
    if recipe is None:
        failures.append(f"Recipe '{slug}' is missing from {javascript_path}.")
    elif f"figmaNode: '{node_id}'" not in recipe:
        failures.append(f"Recipe '{slug}' does not contain figmaNode: '{node_id}'.")

    picker = picker_block(blade, slug)
    if picker is None:
        failures.append(f"Picker button for '{slug}' is missing from {blade_path}.")
    else:
        if f'data-figma-node="{node_id}"' not in picker:
            failures.append(f"Picker '{slug}' does not contain data-figma-node=\"{node_id}\".")
        if f"/img/youtube-templates/{slug}-preview.png" not in picker:
            failures.append(f"Picker '{slug}' does not use the canonical preview path.")

    if f".artboard--{slug}" not in css:
        failures.append(f"CSS hook '.artboard--{slug}' is missing from {css_path}.")

    preview_path = assets_path / f"{slug}-preview.png"
    dimensions = png_dimensions(preview_path)
    if dimensions is None:
        failures.append(f"Preview {preview_path} is missing or is not a readable PNG.")
    elif dimensions != (1280, 720):
        failures.append(f"Preview {preview_path} is {dimensions[0]} × {dimensions[1]}; expected 1280 × 720.")

    for asset in arguments.asset:
        asset_path = assets_path / asset
        if not asset_path.is_file():
            failures.append(f"Required asset is missing: {asset_path}")

    temporary_url_files = find_temporary_urls(root)
    for temporary_url_file in temporary_url_files:
        failures.append(f"Temporary Figma asset URL remains in {temporary_url_file}.")

    if failures:
        print(f"Template audit failed for '{slug}':", file=sys.stderr)
        for failure in failures:
            print(f"- {failure}", file=sys.stderr)
        return 1

    print(f"Template audit passed for '{slug}' ({node_id}).")
    print(f"Preview: {preview_path} (1280 × 720)")
    print(f"Required assets checked: {len(arguments.asset)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
