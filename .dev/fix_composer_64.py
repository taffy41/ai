#!/usr/bin/env python3
"""Widen Symfony version constraints in src/** and examples for Symfony 6.4 support.

Idempotent. See SYMFONY-6.4-BACKPORT.md for the full resync procedure.
Usage:  python3 .dev/fix_composer_64.py
"""
import json
import glob
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
files = glob.glob(os.path.join(ROOT, "src", "**", "composer.json"), recursive=True)
files += glob.glob(os.path.join(ROOT, "examples", "composer.json"))

LEAVE_EXACT = {
    "symfony/event-dispatcher-contracts", "symfony/http-client-contracts",
    "symfony/service-contracts", "symfony/translation-contracts",
    "symfony/models-dev", "symfony/monolog-bundle",
}
SKIP = {"symfony/json-path"}            # 7.3-only, require-dev, guarded by class_exists()
TARGET = "^6.4|^7.0|^8.0"


def new_value(key, value):
    if key == "symfony/type-info":
        return "^7.2|^8.0"              # uses NullableType (7.2)
    if key in SKIP or key in LEAVE_EXACT or key.startswith("symfony/ai-"):
        return None
    if key.startswith("symfony/polyfill-") or not key.startswith("symfony/"):
        return None
    if "6.4" in value or "5.4" in value:
        return None                      # already 6.4-compatible
    if re.fullmatch(r"\^7\.\d+(\|\^8\.0)?", value):
        return TARGET
    return None


def main():
    total = 0
    for f in files:
        text = open(f).read()
        data = json.loads(text)
        changes = []
        for section in ("require", "require-dev"):
            for k, v in data.get(section, {}).items():
                nv = new_value(k, v)
                if nv and nv != v:
                    changes.append((k, v, nv))
        for k, v, nv in changes:
            text = text.replace('"%s": "%s"' % (k, v), '"%s": "%s"' % (k, nv))
        if changes:
            open(f, "w").write(text)
            print(os.path.relpath(f, ROOT))
            for k, v, nv in changes:
                print("   %-40s %s -> %s" % (k, v, nv))
                total += 1
    print("\nTotal constraint changes:", total)


if __name__ == "__main__":
    main()
