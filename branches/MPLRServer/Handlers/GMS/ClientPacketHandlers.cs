using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
#if LOCALE_GMS
    public class ClientPacketHandlers : IClientPacketHandlers
    {
        private static ClientPacketHandlers _instance;
        public static ClientPacketHandlers Instance { get { return _instance = _instance ?? new ClientPacketHandlers(); } }
    }
#endif
}