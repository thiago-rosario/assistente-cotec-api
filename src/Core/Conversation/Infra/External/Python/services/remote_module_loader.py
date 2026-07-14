import importlib.util
import sys
from pathlib import Path
from types import ModuleType


class RemoteModuleLoader:
    def load(self, module_name: str, module_content: bytes) -> ModuleType:
        spec = importlib.util.spec_from_loader(module_name, loader=None)
        remote_module = importlib.util.module_from_spec(spec)

        sys.modules["globals"] = self._create_globals_module()
        exec(module_content, remote_module.__dict__)
        sys.modules[module_name] = remote_module

        return remote_module

    def _create_globals_module(self) -> ModuleType:
        globals_module = ModuleType("globals")
        globals_module.drivers = {}
        globals_module.filas = {}
        globals_module.enquetes_processadas = set()
        globals_module.ROOT_DIR = Path.cwd()

        return globals_module
