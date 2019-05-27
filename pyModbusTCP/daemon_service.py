import sys, modbusmaster, argparse
from daemon import Daemon

class MyDaemon(Daemon):
    def run(self):
        pars = self.parsing()
        arg = pars.parse_args(sys.argv[1:])
        modbusmaster.getPlcData(arg)

    def parsing(self):
        parser = argparse.ArgumentParser()
        parser.add_argument('start', nargs='?')
        parser.add_argument('--plcip', required=True)
        parser.add_argument('-r', '--rabbitmqip', required=True)
        parser.add_argument('-e', '--exchange', required=True)
        parser.add_argument('-q', '--queue', required=True)
        parser.add_argument('--pubkey', required=True)
        parser.add_argument('-s', '--sleep', required=True, type=float)
        return parser

if __name__ == "__main__":
    daemon = MyDaemon('/tmp/HMTd.pid')
    if len(sys.argv) > 1:
        if 'start' == sys.argv[1]:
            daemon.start()
        elif 'stop' == sys.argv[1]:
            daemon.stop()
        elif 'restart' == sys.argv[1]:
            daemon.restart()
        else:
            print "Unknown command"
            sys.exit(2)
        sys.exit(0)
    else:
        print "usage: %s start|stop|restart" % sys.argv[0]
        sys.exit(2)