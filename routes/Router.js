class Router {

  constructor(app) {
    this.app = app;
  }

  get (route, callback) {
    this.app.get("/api" + route, async (request, res) => {
      try {
        let result = await callback(request);
        res.json(result);
      } catch (error) {
        console.error(error);
        res.json(error);
      }
    });
  }

  post (route, callback) {
    this.app.post("/api" + route, async (request, res) => {
      try {

        let result = await callback(request);

        res.json(result);
      } catch (error) {
        console.error(error);
        res.json(error);
      }
    });
  }

  put (route, callback) {
    this.app.put("/api" + route, async (request, res) => {
      try {

        let result = await callback(request);

        res.json(result);
      } catch (error) {
        console.error(error);
        res.json(error);
      }
    });
  }

  delete (route, callback) {
    this.app.delete("/api" + route, async (request, res) => {
      try {

        let result = await callback(request);

        res.json(result);
      } catch (error) {
        console.error(error);
        res.json(error);
      }
    });
  }
}

module.exports = Router; 