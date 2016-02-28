<?php
namespace LukeNZ\Reddit;

abstract class TokenStorageMethod {
    const Cookie = 1;
    const Redis = 2;
    const File = 3;
}