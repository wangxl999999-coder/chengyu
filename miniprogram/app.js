App({
  globalData: {
    userInfo: null,
    openid: null,
    serverUrl: 'https://你的域名/api'
  },

  onLaunch: function () {
    this.checkLogin();
  },

  checkLogin: function () {
    var openid = wx.getStorageSync('openid');
    if (openid) {
      this.globalData.openid = openid;
      this.getUserInfo();
    } else {
      this.wxLogin();
    }
  },

  wxLogin: function () {
    var that = this;
    wx.login({
      success: function (res) {
        if (res.code) {
          wx.request({
            url: that.globalData.serverUrl + '/login.php',
            method: 'POST',
            data: {
              code: res.code
            },
            success: function (res) {
              if (res.data.status === 'success') {
                that.globalData.openid = res.data.openid;
                wx.setStorageSync('openid', res.data.openid);
                that.getUserInfo();
              }
            }
          });
        }
      }
    });
  },

  getUserInfo: function () {
    var that = this;
    wx.request({
      url: that.globalData.serverUrl + '/user.php',
      method: 'GET',
      data: {
        openid: that.globalData.openid
      },
      success: function (res) {
        if (res.data.status === 'success') {
          that.globalData.userInfo = res.data.data;
        }
      }
    });
  },

  request: function (options) {
    var that = this;
    options.url = that.globalData.serverUrl + options.url;
    if (!options.data) {
      options.data = {};
    }
    options.data.openid = that.globalData.openid;
    options.header = {
      'Content-Type': 'application/x-www-form-urlencoded'
    };
    return new Promise(function (resolve, reject) {
      wx.request({
        ...options,
        success: resolve,
        fail: reject
      });
    });
  }
});
