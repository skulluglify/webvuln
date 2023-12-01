<?php

enum PathSys: string
{
    case WINDOWS = 'Windows';  // C:\\, D:\\
    case POSIX = 'Posix';  // /home/user
    case NETWORK = 'Network';  // file:// ftp://
    case UNKNOWN = 'Unknown';
}
