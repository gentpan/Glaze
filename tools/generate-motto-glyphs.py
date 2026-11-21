"""Convert the supplied WOFF2 subsets to compact glyph outlines for the theme."""

import argparse
import gzip
import json
import struct
from collections import defaultdict
from pathlib import Path

from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.ttLib import TTFont


parser = argparse.ArgumentParser()
parser.add_argument("output", type=Path)
parser.add_argument("fonts", nargs="+", type=Path)
parser.add_argument("--axis", action="append", default=[], metavar="TAG=VALUE")
arguments = parser.parse_args()
axes = {tag: float(value) for tag, value in (setting.split("=", 1) for setting in arguments.axis)}


def svg_number(number):
    return f"{number:.1f}".rstrip("0").rstrip(".")

metrics = None
pages = defaultdict(dict)
for path in sorted(arguments.fonts):
    with TTFont(path) as font:
        font_metrics = [font["head"].unitsPerEm, font["hhea"].ascent, -font["hhea"].descent]
        if metrics is None:
            metrics = font_metrics
        if metrics != font_metrics:
            raise ValueError(f"Font metrics differ in {path}")

        outlines = font.getGlyphSet(location=axes or None)
        for codepoint, name in font.getBestCmap().items():
            character = chr(codepoint)
            page = pages[character.encode()[:2].hex()]
            if character in page:
                continue
            pen = SVGPathPen(outlines, ntos=svg_number)
            glyph = outlines[name]
            glyph.draw(pen)
            page[character] = [round(glyph.width), pen.getCommands()]

index = {}
data = bytearray()
for key, glyphs in pages.items():
    document = json.dumps(glyphs, ensure_ascii=False, separators=(",", ":")).encode()
    chunk = gzip.compress(document, mtime=0)
    index[key] = [len(data), len(chunk)]
    data.extend(chunk)

header = json.dumps([metrics, index], separators=(",", ":")).encode()
arguments.output.write_bytes(struct.pack(">I", len(header)) + header + data)
