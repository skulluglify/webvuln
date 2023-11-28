<?php

enum PathSys: string
{
    case WINDOWS = 'WINDOWS';  // C:\\, D:\\
    case POSIX = 'POSIX';  // /home/user
    case NETWORK = 'NETWORK';  // file:// ftp://
    case UNKNOWN = 'UNKNOWN';
}
