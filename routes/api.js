const { route } = require("express/lib/application");
const Router = require("./Router");

//Classes
const AdminController = require('../controller/admin.controller');
const SpecialistController = require('../controller/specialist.controller');
const ServiceController = require("../controller/service.controller");
const ClientController = require("../controller/client.controller");

//Initialization
const admin = new AdminController();
const specialist = new SpecialistController();
const client = new ClientController();
const service = new ServiceController();

module.exports = initApi = (app) => {

  /**
   * Create router
   */
  const router = new Router(app);

  router.get("/admin/all", admin.getList);
  router.post("/admin/authenticate", admin.authenticate);

  router.get("/specialist/all", specialist.getList);
  router.get("/specialist/active", specialist.getActive);
  router.get("/specialist/:id", specialist.getById);
  router.post("/specialist/authenticate", specialist.authenticate);
  router.post("/specialist", specialist.save);
  router.post("/specialist/:id", specialist.save);
  router.delete("/specialist/:id", specialist.deleteById);

  router.get("/client/all", client.getList);
  router.get("/client/active", client.getActive);
  router.get("/client/:id", client.getById);
  router.post("/client/authenticate", client.authenticate);
  router.post("/client", client.save);
  router.post("/client/:id", client.save);
  router.delete("/client/:id", client.deleteById);

  router.get("/service/all", service.getList);
  router.get("/service/:id", service.getById);
  router.post("/service", service.save);
  router.post("/service/:id", service.save);
  router.delete("/service/:id", service.deleteById);
  
}