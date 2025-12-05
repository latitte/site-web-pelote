const WebSocket = require('ws');
const server = new WebSocket.Server({ port: 8080 });

let viewers = [];

server.on('connection', ws => {
  ws.on('message', msg => {
    if (msg.toString() === 'sender') {
      ws.isSender = true;
      console.log("📡 Arbitre connecté");
    } else if (msg.toString() === 'viewer') {
      viewers.push(ws);
      console.log("👀 Spectateur connecté");
    } else {
      viewers.forEach(viewer => {
        if (viewer.readyState === WebSocket.OPEN) {
          viewer.send(msg);
        }
      });
    }
  });

  ws.on('close', () => {
    viewers = viewers.filter(v => v !== ws);
  });
});

console.log("🚀 WebSocket serveur lancé sur ws://localhost:8080");
