const { route } = require("express/lib/application");
const Router = require("./Router");

//Classes
const AdminController = require('../controller/admin.controller');

//Initialization
const admin = new AdminController();

module.exports = initApi = (app) => {

  /**
   * Create router
   */
  const router = new Router(app);

  router.get("/admin/all", admin.getAllUsers);
  router.post("/admin/authenticate", admin.authenticate);
}