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
#elif LOCALE_EMS
        public const byte LOCALE = 9;
        public const ushort VERSION = 94;
#elif LOCALE_KMS
        public const byte LOCALE = 1;
        public const ushort VERSION = -1;
#endif
    }
}
