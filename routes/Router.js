class Router {

  constructor(app) {
    this.app = app;
  }

  get (route, callback) {
    this.app.get("/api" + route, async (request, res) => {
      try {
        let result = await callback();
        res.json(result);
      } catch (error) {
        console.log(error)
        res.json({
          success: 0,
          error: error,
        });
      }
    });
  }

  post (route, callback) {
    this.app.post("/api" + route, async (request, res) => {
      try {

        let result = await callback(request.body);

        res.json(result);
      } catch (error) {
        console.error(error);
        res.json({
          success: 0,
          error: error
        });
      }
    });
  }

  put (route, callback) {
    this.app.post("/api" + route, async (request, res) => {
      try {

        let result = await callback(request.body);

        res.json(result);
      } catch (error) {
        console.error(error);
        res.json({
          success: 0,
          error: error
        });
      }
    });
  }

  delete (route, callback) {
    this.app.post("/api" + route, async (request, res) => {
      try {

        let result = await callback(request.body);

        res.json(result);
      } catch (error) {
        console.error(error);
        res.json({
          success: 0,
          error: error
        });
      }
    });
  }
}

module.exports = Router; 