import re
from urllib.parse import urlparse
import tldextract
import math
from collections import Counter

SUSPICIOUS_KEYWORDS = [
    "login", "verify", "update", "secure", "account", "banking", "confirm",
    "password", "signin", "reset", "free", "bonus", "gift", "wallet"
]

def _entropy(s):
    if not s: return 0.0
    p, lns = Counter(s), float(len(s))
    return -sum(count/lns * math.log2(count/lns) for count in p.values())

def extract_url_features(url: str) -> dict:
    url = str(url)
    parsed = urlparse(url)
    ext = tldextract.extract(url)

    hostname = parsed.netloc
    path = parsed.path
    query = parsed.query
    
    num_digits = sum(ch.isdigit() for ch in url)
    suspicious_tlds = ['zip', 'review', 'country', 'kim', 'cricket', 'science', 'work', 'party', 'gq', 'link', 'top', 'xyz']

    features = {
        "url_length": len(url),
        "domain_length": len(ext.domain),
        "path_length": len(path),
        "query_length": len(query),
        "num_dots": url.count("."),
        "num_hyphens": url.count("-"),
        "num_underscores": url.count("_"),
        "num_slashes": url.count("/"),
        "num_at_signs": url.count("@"),
        "num_question_marks": url.count("?"),
        "num_equals": url.count("="),
        "num_digits": num_digits,
        "digit_ratio": num_digits / max(len(url), 1),
        "has_ip_address": int(bool(re.search(r"(\d{1,3}\.){3}\d{1,3}", hostname))),
        "is_https": int(parsed.scheme.lower() == "https"),
        "is_suspicious_tld": int(ext.suffix.lower() in suspicious_tlds),
        "num_subdomains": len(ext.subdomain.split('.')) if ext.subdomain else 0,
        "is_url_shortener": int(any(s in hostname.lower() for s in ["bit.ly", "tinyurl", "goo.gl", "t.co", "ow.ly", "is.gd", "buff.ly", "adf.ly"])),
        "domain_has_digit": int(any(ch.isdigit() for ch in ext.domain)),
        "domain_hyphen_count": ext.domain.count("-"),
        "suspicious_keyword_count": sum(url.lower().count(k) for k in SUSPICIOUS_KEYWORDS),
        "has_login_keyword": int("login" in url.lower() or "signin" in url.lower()),
        "has_verify_keyword": int("verify" in url.lower() or "confirm" in url.lower()),
        "has_bank_keyword": int("bank" in url.lower() or "account" in url.lower() or "wallet" in url.lower()),
        "has_free_keyword": int("free" in url.lower() or "bonus" in url.lower() or "gift" in url.lower()),
        "has_secure_keyword": int("secure" in url.lower()),
        "domain_entropy": _entropy(ext.domain),
        "path_entropy": _entropy(path),
        "has_double_slash": int("//" in path),
        "has_hex_encoding": int("%" in url)
    }
    return features