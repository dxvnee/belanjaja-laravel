"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.useLoading = useLoading;

var _vue = require("vue");

var isLoading = (0, _vue.ref)(false);
var message = (0, _vue.ref)("");

function useLoading() {
  var loading = function loading(msg, fn) {
    return regeneratorRuntime.async(function loading$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            isLoading.value = true;
            message.value = msg;
            _context.prev = 2;
            _context.next = 5;
            return regeneratorRuntime.awrap(fn());

          case 5:
            _context.prev = 5;
            isLoading.value = false;
            return _context.finish(5);

          case 8:
          case "end":
            return _context.stop();
        }
      }
    }, null, null, [[2,, 5, 8]]);
  };

  return {
    loading: loading,
    isLoading: isLoading,
    message: message
  };
}