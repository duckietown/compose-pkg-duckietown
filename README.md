# compose-pkg-duckietown

Duckietown package for the \compose\ platform implementing **Duckieboard**, a Remote Dashboard for the Duckietown project.

Duckieboard provides high level fleet management and monitoring capabilities for Duckietown.


## Duckietown <-> World

The easiest way to monitor and control a Duckiebot remotely using Duckieboard, thus via the web browser,
is to setup a network bridge between the outside world and Duckietown.

Bridging each Duckiebot to the outside world has the main advantage of allowing data to
flow directly from the on-board Raspberry Pi to the user's browser. The Duckiebots are bridged
using *IP Forwarding* via a dedicated machine (i.e., network bridge) connected to both Duckietown
and internet. Deploying the network bridge on the machine hosting the Duckieboard would allow the
use of Duckieboard to easily configure the routing table (iptables) of the network bridge.
For the remainder of this document we will refer to the bridge machine as Bridge.

The applications bridged are:

- SSH [RPi port: TCP(`22`), Bridge port: TCP(`41XXX`)]
- ROS Bridge [RPi port: TCP(`9090`), Bridge port: TCP(`42XXX`)]

where `XXX` above is a unique ID associated to each Duckiebot.


### IP Forwarding

These instructions are based on tests run on Duckiebots and Bridge both
running Ubuntu 16.04.

#### Enabling IP Forwarding (duckieboard)

IP Forwarding is a kernel function that can be enabled on the Bridge server by running

```
sysctl net.ipv4.ip_forward=1
```

for a temporary effect or uncomment the line

```
net.ipv4.ip_forward = 1
```

in `/etc/sysctl.conf` for a permanent effect.


#### Enable IP Masquerade

IP Masquerade allows machines within a private local network (i.e., `duckietown`) to
seamlessly communicate with the outside world via a mid-point machine (i.e., our Bridge
machine).

IP Masquerade can be enabled by running the following command on the Bridge machine

```
iptables -t nat -A POSTROUTING -j MASQUERADE
```

#### Bridging an Application

The `TCP` port `AAAA` on the Bridge (IP: `CCC.CCC.CCC.CCC`) can be forwarded (bridged) to
the the port `BBBB` on the Raspberry Pi (IP:`DDD.DDD.DDD.DDD`) by running the following
command on the Bridge.

```
iptables -t nat -A PREROUTING -p tcp -d CCC.CCC.CCC.CCC --dport AAAA -j DNAT --to-destination DDD.DDD.DDD.DDD:BBBB
```


#### Monitoring bridged Applications

The list of all bridged applications can be obtained by running the command

```
sudo iptables -t nat -L PREROUTING --line-numbers
```

An example of output is the following

```
Chain PREROUTING (policy ACCEPT)
num  target     prot opt source               destination
1    DNAT       tcp  --  anywhere             128.135.8.123        tcp dpt:40003 to:192.168.2.53:22
```

The output of this command will assign to each rule a unique ID (column `num`) that
we can use to delete rules. A rule with ID `<N>` can be removed by running the command

```
sudo iptables -t nat -D PREROUTING <N>
```


## Process Manager on Duckieboard

The library [`supervisor`](http://supervisord.org/) introduces the possibility to use
Duckieboard as a process monitor and manager for Duckiebots. *supervisor* provides
an XMLRPC interface. This opens two possibilities, (i) bridging the XMLRPC server
to the outside world, (ii) implementing a ROS interface for *supervisor* that maps
XMLRPC resources to ROS Services.

We believe that the best option is the latter, that is implementing a ROS interface
for *supervisor* that maps XMLRPC resources to ROS Services. This option gives us the
extra possibility to correct some issues with *supervisor* monitoring the `roslaunch`
process. In fact, `roslaunch` employs its own process manager to monitor all the nodes
it runs. This process manager would normally mask the presence of running nodes to the
overhead *supervisor* process manager.


## SSH Console via Duckieboard

The library
[GateOne](http://liftoffsoftware.com/Products/GateOne)
(not tested yet) provides an open-source web-based
Terminal Emulator and SSH client. It constitutes a good
candidate for implementing an SSH terminal on
Duckieboard.


## (Secure) ROS Bridge

[ROS Bridge](https://github.com/RobotWebTools/rosbridge_suite) is an open-source library
that allows us to tunnel ROS data through a TCP/UDP/WS connection. We can use it to connect
ROS to Duckieboard by tunneling ROS via a WebSocket (WS) connection.
[roslibjs](https://github.com/RobotWebTools/roslibjs) is a ROS client written in JavaScript
that can communicate with ROS Bridge. By default, WSs do not provide security/authentication
protocols. Nonetheless, ROS Bridge implements a simple MAC-based OAuth1.0 authentication
procedure that uses the [rosauth](https://github.com/GT-RAIL/rosauth) library.
The authentication in ROS Bridge is disabled by default, it can be enabled
[here](https://github.com/RobotWebTools/rosbridge_suite/blob/develop/rosbridge_server/src/rosbridge_server/websocket_handler.py#L48).
For further information about how to construct an authentication token in JS that is compatible
with rosauth look at the implementation of rosauth [here](https://github.com/GT-RAIL/rosauth/blob/develop/src/ros_mac_authentication.cpp).
