using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class ServerMapleInfo
    {
#if LOCALE_GMS
        public const byte LOCALE = 8;
        public const ushort VERSION = 141;
        public const int KEYMAP_SLOTS = 89;
        public const ushort MAPLER_PORT = 23710;
        public const ushort MAPLER_PORT_SERVER_INFO = 23711;
#elif LOCALE_EMS
        public const byte LOCALE = 9;
        public const ushort VERSION = 95;
        public const int KEYMAP_SLOTS = 91;
        public const ushort MAPLER_PORT = 23720;
        public const ushort MAPLER_PORT_SERVER_INFO = 23721;
#elif LOCALE_KMS
        public const byte LOCALE = 1;
        public const ushort VERSION = -1;
        public const int KEYMAP_SLOTS = 89; // EDIT
        public const ushort MAPLER_PORT = 23730;
        public const ushort MAPLER_PORT_SERVER_INFO = 23731;
#endif
    }
}
