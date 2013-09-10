using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
#if LOCALE_GMS
    public class ServerPacketHandlers : IServerPacketHandler
    {
        private static ServerPacketHandlers _instance;
        public static ServerPacketHandlers Instance { get { return _instance = _instance ?? new ServerPacketHandlers(); } }
    }
#endif
}
