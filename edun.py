#!/usr/bin/env python3
# agregator-laravel.py
import os
import re
import argparse
from pathlib import Path
from typing import Set

# =========================================================================
# CONFIGURATION CLASS (Information Expert)
# Memegang seluruh metadata konfigurasi penapisan repositori Laravel
# =========================================================================
class LaravelAggregatorConfig:
    DEFAULT_TARGET = r"C:\Users\PC\Documents\Dev\e-daily\e-daily-report" # Gunakan direktori saat skrip dieksekusi sebagai default
    DEFAULT_OUTPUT = "e-daily-report-context.txt"

    # 1. Folder Blacklist (Diabaikan secara mutlak untuk mereduksi noise)
    FORBIDDEN_DIRS = {
        "vendor", "node_modules", ".git", "storage", "bootstrap/cache", 
        "public/assets", "public/img", "public/data", ".vscode", ".idea"
    }

    # 2. Folder Whitelist (Hanya turun ke folder di bawah ini jika di root)
    # Memastikan kita tidak mengambil folder tidak relevan di root
    ALLOWED_DIRS = {
        "app", "config", "database", "routes", "resources", 
        "tests", "bootstrap", "public"
    }

    # 3. Ekstensi Berkas Teks yang Diizinkan
    INCLUDE_EXTENSIONS = {
        ".php", ".json", ".js", ".css", ".yaml", ".yml", 
        ".md", ".xml", ".csv", ".conf"
    }

    # 4. Berkas Konfigurasi Wajib di Tingkat Root
    ESSENTIAL_ROOT_FILES = {
        "composer.json", "composer.lock", "package.json", "package-lock.json", 
        ".env.example", ".env", "artisan", "phpunit.xml", "docker-compose.yml", 
        "Dockerfile", ".gitignore"
    }

    # 5. Batas Maksimum Ukuran Berkas (1 MB)
    MAX_FILE_SIZE_BYTES = 1024 * 1024  

    # Pre-compiled Regex untuk Sensor Keamanan Kunci Privat
    # Mencegah ekstraksi berkas SSL atau credential secara tidak sengaja
    SENSITIVE_REGEX = re.compile(
        r"(key.*\.pem|.*\.key|id_rsa.*|credentials.*|.*secret.*)", 
        re.IGNORECASE
    )


# =========================================================================
# OPTIMIZER CLASS (Pure Fabrication)
# Pintu gerbang optimasi konten teks dan perlindungan I/O dari binary
# =========================================================================
class LLMContextOptimizer:
    @staticmethod
    def compress_code(content: str) -> str:
        """Kompmpresi token LLM dengan melucuti spasi trailing dan baris kosong ganda berlebih."""
        lines = content.splitlines()
        optimized_lines = []
        previous_empty = False
        
        for line in lines:
            stripped = line.rstrip()
            is_empty = len(stripped) == 0
            
            if is_empty and previous_empty:
                continue
                
            optimized_lines.append(stripped)
            previous_empty = is_empty
            
        return "\n".join(optimized_lines)

    @staticmethod
    def is_binary(file_path: Path) -> bool:
        """Deteksi biner efisien melalui pembacaan header byte (magic number bypass)."""
        try:
            with open(file_path, 'rb') as f:
                return b'\x00' in f.read(512)
        except Exception:
            return True


# =========================================================================
# AGGREGATOR CONTROLLER (Controller Pattern)
# Mengorkestrasi sistem berkas (os.walk) dan merakit bundel akhir
# =========================================================================
class LaravelCodebaseAggregator:
    def __init__(self, target_dir: str, output_name: str):
        self.config = LaravelAggregatorConfig()
        self.optimizer = LLMContextOptimizer()
        
        self.target_path = Path(target_dir).resolve()
        if not self.target_path.is_dir():
            self.target_path = Path(__file__).parent.resolve()
            print(f"[SYSTEM] Peringatan: Target direktori tidak valid. Menggunakan fallback di: {self.target_path}")

        self.output_file = self.target_path / output_name
        self.config.ESSENTIAL_ROOT_FILES.add(output_name) 

    def _parse_gitignore(self) -> Set[str]:
        """Ekstraksi diskret dari .gitignore untuk melengkapi rule FORBIDDEN_DIRS."""
        ignored_items = set()
        gitignore_path = self.target_path / ".gitignore"
        if gitignore_path.exists():
            try:
                for line in gitignore_path.read_text("utf-8").splitlines():
                    line = line.strip()
                    if line and not line.startswith("#"):
                        clean_line = line.replace("/", "").replace("*", "")
                        if clean_line:
                            ignored_items.add(clean_line)
            except Exception as e:
                print(f"[WARN] Anomali saat memproses .gitignore: {e}")
        return ignored_items

    def execute(self):
        print(f"🔍 [INIT] Menganalisa struktur Laravel di: {self.target_path}")
        
        git_ignored = self._parse_gitignore()
        forbidden_dirs_union = self.config.FORBIDDEN_DIRS.union(git_ignored)
        forbidden_files_union = git_ignored

        file_count = 0
        original_total_size = 0
        compressed_total_size = 0

        try:
            with open(self.output_file, "w", encoding="utf-8") as out_file:
                out_file.write("=== STRUKTUR & ISI KODE (LARAVEL MONOLITH MODE) ===\n\n")

                for root, dirs, files in os.walk(self.target_path):
                    root_path = Path(root)
                    relative_root = root_path.relative_to(self.target_path)
                    is_root_level = len(relative_root.parts) == 0

                    # 1. Pruning subdirektori terlarang untuk efisiensi kompleksitas waktu O(N)
                    dirs[:] = [d for d in dirs if d not in forbidden_dirs_union]

                    # 2. Isolasi pencarian root-level agar direktori liar terbuang
                    if is_root_level:
                        dirs[:] = [d for d in dirs if d in self.config.ALLOWED_DIRS]

                    for file_name in files:
                        file_path = root_path / file_name
                        relative_file_path = file_path.relative_to(self.target_path)
                        is_root_file = len(relative_file_path.parts) == 1

                        is_essential_root = is_root_file and file_name in self.config.ESSENTIAL_ROOT_FILES

                        # Logika Penapisan Defensif
                        if file_name in forbidden_files_union:
                            continue
                        
                        if not is_essential_root and self.config.SENSITIVE_REGEX.match(file_name):
                            continue

                        if is_root_file and not is_essential_root:
                            continue

                        if not is_essential_root and file_path.suffix not in self.config.INCLUDE_EXTENSIONS:
                            continue
                            
                        # Bypass perlindungan ukuran berkas untuk menghindari buffer overflow memory
                        try:
                            if file_path.stat().st_size > self.config.MAX_FILE_SIZE_BYTES:
                                continue
                        except Exception:
                            continue

                        if self.optimizer.is_binary(file_path):
                            continue

                        # Agregasi & Kompresi Eksekusi
                        try:
                            file_size = file_path.stat().st_size
                            content = file_path.read_text("utf-8", errors="ignore")
                            compressed_content = self.optimizer.compress_code(content)

                            out_file.write(f"\n--- FILE: {relative_file_path} ---\n")
                            out_file.write(compressed_content)
                            out_file.write("\n")

                            file_count += 1
                            original_total_size += file_size
                            compressed_total_size += len(compressed_content.encode('utf-8'))
                            print(f"-> Diindeks: {relative_file_path}")

                        except Exception as e:
                            print(f"-> Terminated {relative_file_path}: {e}")

            print(f"\n✅ Build Selesai. {file_count} komponen tergabung di:\n   {self.output_file}")
            print(f"📊 Original Size : {original_total_size / 1024:.2f} KB")
            print(f"🚀 Context Size  : {compressed_total_size / 1024:.2f} KB")
            
            if original_total_size > 0:
                saving_percent = ((original_total_size - compressed_total_size) / original_total_size) * 100
                print(f"📉 LLM Token Saving: ~{saving_percent:.1f}%")

        except Exception as e:
            print(f"[FATAL] I/O Stream Exception: {e}")


# =========================================================================
# SYSTEM ENTRY
# =========================================================================
if __name__ == "__main__":
    config_default = LaravelAggregatorConfig()

    parser = argparse.ArgumentParser(description="Laravel Codebase Context Aggregator")
    parser.add_argument("--dir", type=str, default=config_default.DEFAULT_TARGET, help="Direktori target (default: direktori aktif)")
    parser.add_argument("--out", type=str, default=config_default.DEFAULT_OUTPUT, help="Nama berkas kompilasi")
    
    args = parser.parse_args()

    aggregator = LaravelCodebaseAggregator(target_dir=args.dir, output_name=args.out)
    aggregator.execute()