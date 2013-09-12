namespace MPLRServer
{
#if LOCALE_EMS

    public enum ServerOpcodes : ushort
    {
        LOGIN_NORMAL = 0x0000,
        LOGIN_AFTER_WORLDSELECT = 0x2000, // Not used in EMS
        CHARACTER_CHOOSE = 0x0008,
        CHARACTER_DELETE = 0x100E,

        CONNECT_TO_SERVER = 0x000D,
        PING = 0x000E,

        INVENTORY_UPDATE = 0x001D,
        INVENTORY_SLOTS_UPDATE,
        STAT_UPDATE,
        SKILL_UPDATE = 0x002C,

        MESSAGE = 0x002A,

        BUDDYLIST = 0x005D,
        GUILD = 0x005F,
        ALLIANCE,

        FAMILIARS = 0x0081,

        ABILITY_UPDATE = 0x00D2,

        MAPLEPOINTS = 0x00E6,

        SKILL_MACROS = 0x00E2,
        CHANGE_MAP,

        SPAWN_PLAYER = 0x0117,

        SPAWN_ANDROID = 0x0145,

        KEYMAP_UPDATE = 0x028C,
    }

    public enum ClientOpcodes : ushort
    {
        VERSION_INFO = 0x0014,
        SELECT_CHANNEL = 0x001A,
        CHARACTER_LOAD = 0x0022,
        PONG = 0x002B,
        CHANGE_CHANNEL_REQUEST = 0x0052,

        WHISPER = 0x1100,

        KEYMAP_UPDATE = 0x00FD
    }
#endif
}