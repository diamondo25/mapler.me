using System;
using System.Collections.Generic;
using System.Collections.Concurrent;
using System.Linq;
using System.Text;

using System.Threading;

namespace MPLRServer
{
    public class MasterThread
    {
        public class RepeatingAction
        {
            public string Name { get; private set; }
            public bool RepeatIsTicks { get; private set; }
            public ulong Repeat { get; private set; }
            public ulong TicksNextRun { get; private set; }
            public Action<DateTime> Action { get; private set; }
            public DateTime LastRun { get; private set; }
            public DateTime NextRun { get; private set; }
            private bool isInForRemoval = false;

            public RepeatingAction(string pName, Action<DateTime> pAction, ulong pStart, ulong pRepeat, bool pRepeatIsTicks = false)
            {
                Name = pName;
                RepeatIsTicks = pRepeatIsTicks;
                Repeat = pRepeat;
                Action = pAction;

                if (RepeatIsTicks)
                {
                    TicksNextRun = MasterThread.Instance.CurrentTickCount + Repeat;
                }
                else
                {
                    NextRun = MasterThread.CurrentDate.AddMilliseconds(pStart);
                }
            }

            public void TryRun(DateTime pCurrentDate)
            {
                if (isInForRemoval) return;
                if (RepeatIsTicks)
                {
                    if (MasterThread.Instance.CurrentTickCount < TicksNextRun) return;
                    if (Repeat == 0) isInForRemoval = true;
                    else TicksNextRun = MasterThread.Instance.CurrentTickCount + Repeat;
                }
                else
                {
                    if ((pCurrentDate - NextRun).TotalMilliseconds < 0) return;
                    if (Repeat == 0) isInForRemoval = true;
                    else NextRun = MasterThread.CurrentDate.AddMilliseconds(Repeat);
                }
                Action(pCurrentDate);
                LastRun = pCurrentDate;
                if (isInForRemoval)
                {
                    MasterThread.Instance.RemoveRepeatingAction(Name, null);
                }
            }
        }

        public static MasterThread Instance { get; private set; }

        public static DateTime CurrentDate { get; private set; }

        public ulong TicksPerSecond { get; private set; }
        public ulong TicksBeforeSleep { get; set; }
        public ulong CurrentTickCount { get; private set; }
        public bool Stop { get; set; }
        public string ServerName { get; private set; }

        private ConcurrentQueue<Action<DateTime>> _callbacks = new ConcurrentQueue<Action<DateTime>>();
        private List<RepeatingAction> _repeatingActions = new List<RepeatingAction>();

        private Thread _mainThread;

        List<ulong> _performanceCounter = new List<ulong>();

        public MasterThread(string pServerName, ulong pTicksBeforeSleep)
        {
            ServerName = pServerName;
            TicksBeforeSleep = pTicksBeforeSleep;
            Stop = false;

        }

        public static void Load(string pServerName, ulong pTicksBeforeSleep = 500)
        {
            Instance = new MasterThread(pServerName, pTicksBeforeSleep);
            Instance.Init();
        }

        public void Init()
        {
            _mainThread = new Thread(Run);
            _mainThread.IsBackground = true;
            _mainThread.Name = "MasterThread - thread";
            _mainThread.Priority = ThreadPriority.AboveNormal;
            _mainThread.Start();
        }

        public void AddRepeatingAction(RepeatingAction pAction)
        {
            AddCallback((a) =>
            {
                _repeatingActions.Add(pAction);
            });
        }

        /// <summary>
        /// This function removes an repeating action synchronously from the list.
        /// </summary>
        /// <param name="pName">Name of the Repeating Action</param>
        /// <param name="pOnRemoved">Callback when the removal is performed.</param>
        public void RemoveRepeatingAction(string pName, Action<DateTime, string, bool> pOnRemoved)
        {
            RepeatingAction action = _repeatingActions.First((a) => { return a.Name == pName; });
            if (pOnRemoved == null) return;
            if (action == null)
            {
                pOnRemoved(CurrentDate, pName, false);
            }
            else
            {
                AddCallback((a) =>
                {
                    pOnRemoved(a, pName, _repeatingActions.Remove(action)); // Sync
                });
            }
        }

        public void AddCallback(Action<DateTime> pAction)
        {
            _callbacks.Enqueue(pAction);
        }

        private void Run()
        {
            Action<DateTime> action;
            CurrentDate = DateTime.Now;
            try
            {
                for (CurrentTickCount = 0; !Stop; CurrentTickCount++)
                {
                    while (_callbacks.TryDequeue(out action))
                    {
                        try
                        {
                            action(CurrentDate);
                        }
                        catch (Exception ex)
                        {
                            Logger.WriteLine("Caught an exception inside the MainThread thread while running an action. Please, handle the exceptions yourself!\r\n{0}", ex.ToString());
                        }
                    }


                    foreach (var ra in _repeatingActions)
                    {
                        try
                        {
                            ra.TryRun(CurrentDate);
                        }
                        catch (Exception ex)
                        {
                            Logger.WriteLine("Caught an exception inside the MainThread thread while running an action. Please, handle the exceptions yourself!\r\n{0}", ex.ToString());
                        }
                    }

                    if (CurrentTickCount % TicksBeforeSleep == 0)
                    {
                        CurrentDate = DateTime.Now;
                        Thread.Sleep(1);
                    }
                }

            }
            catch { }
            _mainThread = null;
        }
    }
}