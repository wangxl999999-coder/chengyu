App({
  globalData: {
    userInfo: null,
    openid: null,
    serverUrl: 'http://baiozhu.com',
    isDevMode: true,
    mockLevel: 1
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
    
    if (this.globalData.isDevMode) {
      console.log('开发模式：使用模拟登录');
      var mockOpenid = 'dev_openid_' + Date.now();
      that.globalData.openid = mockOpenid;
      wx.setStorageSync('openid', mockOpenid);
      
      that.globalData.userInfo = {
        id: 1,
        nickname: '测试用户',
        avatar_url: '',
        current_level: that.globalData.mockLevel,
        completed_levels: 0,
        total_score: 0
      };
      return;
    }

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
              if (res.data && res.data.status === 'success') {
                that.globalData.openid = res.data.openid;
                wx.setStorageSync('openid', res.data.openid);
                that.getUserInfo();
              } else {
                console.log('登录失败:', res.data);
                that.useMockData();
              }
            },
            fail: function (err) {
              console.log('网络请求失败:', err);
              that.useMockData();
            }
          });
        }
      },
      fail: function () {
        console.log('wx.login 失败');
        that.useMockData();
      }
    });
  },

  getUserInfo: function () {
    var that = this;
    
    if (this.globalData.isDevMode && this.globalData.userInfo) {
      return;
    }

    wx.request({
      url: that.globalData.serverUrl + '/user.php',
      method: 'GET',
      data: {
        openid: that.globalData.openid
      },
      success: function (res) {
        if (res.data && res.data.status === 'success') {
          that.globalData.userInfo = res.data.data;
        }
      },
      fail: function () {
        console.log('获取用户信息失败');
      }
    });
  },

  useMockData: function () {
    console.log('使用模拟数据模式');
    var mockOpenid = 'mock_openid_' + Date.now();
    this.globalData.openid = mockOpenid;
    wx.setStorageSync('openid', mockOpenid);
    
    this.globalData.userInfo = {
      id: 1,
      nickname: '游客用户',
      avatar_url: '',
      current_level: 1,
      completed_levels: 0,
      total_score: 0
    };
  },

  request: function (options) {
    var that = this;
    
    if (this.globalData.isDevMode) {
      console.log('开发模式：模拟API请求', options.url);
      return this.mockRequest(options);
    }

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
        fail: function (err) {
          console.log('API请求失败:', err);
          reject(err);
        }
      });
    });
  },

  mockRequest: function (options) {
    var that = this;
    var mockData = {};
    var url = options.url;
    
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        if (url.indexOf('level.php') !== -1) {
          var level = options.data ? options.data.level : 1;
          if (options.data && options.data.action === 'complete') {
            that.globalData.userInfo.current_level = level + 1;
            if (level > that.globalData.userInfo.completed_levels) {
              that.globalData.userInfo.completed_levels = level;
            }
            mockData = {
              status: 'success',
              data: that.globalData.userInfo
            };
          } else {
            var levelIdioms = {
              1: [
                { id: 1, idiom: '一心一意', pinyin: 'yī xīn yī yì', explanation: '形容专心一意，一门心思只做一件事。', source: '《三国志·魏志·杜恕传》', difficulty: 1, hint: '形容专心' },
                { id: 2, idiom: '三心二意', pinyin: 'sān xīn èr yì', explanation: '形容犹豫不决，意志不坚定或用心不专一。', source: '元·关汉卿《救风尘》', difficulty: 1, hint: '形容不专心' }
              ],
              2: [
                { id: 3, idiom: '四面八方', pinyin: 'sì miàn bā fāng', explanation: '指各个方面或各个地方。', source: '宋·释道原《景德传灯录》', difficulty: 1, hint: '各个方向' },
                { id: 4, idiom: '五颜六色', pinyin: 'wǔ yán liù sè', explanation: '形容色彩复杂或花样繁多。', source: '清·李汝珍《镜花缘》', difficulty: 1, hint: '多种颜色' }
              ],
              3: [
                { id: 5, idiom: '七上八下', pinyin: 'qī shàng bā xià', explanation: '形容心情起伏不定，心神不安。', source: '明·施耐庵《水浒全传》', difficulty: 1, hint: '形容不安' },
                { id: 6, idiom: '九牛一毛', pinyin: 'jiǔ niú yī máo', explanation: '比喻极大数量中极微小的数量。', source: '汉·司马迁《报任少卿书》', difficulty: 1, hint: '形容微小' },
                { id: 7, idiom: '十全十美', pinyin: 'shí quán shí měi', explanation: '十分完美，毫无欠缺。', source: '《周礼·天官冢宰下·医师》', difficulty: 1, hint: '形容完美' }
              ]
            };
            
            var idioms = levelIdioms[level] || levelIdioms[1];
            
            mockData = {
              status: 'success',
              data: {
                level_id: level,
                level_num: level,
                title: '第' + level + '关',
                idiom_count: idioms.length,
                difficulty: level > 2 ? 2 : 1,
                idioms: idioms
              }
            };
          }
        } else if (url.indexOf('notebook.php') !== -1) {
          var action = options.data ? options.data.action : '';
          if (action === 'count') {
            mockData = { status: 'success', data: { count: 0 } };
          } else if (action === 'list') {
            mockData = { status: 'success', data: [] };
          } else if (action === 'check') {
            mockData = { status: 'success', data: { exists: false } };
          } else if (action === 'add' || action === 'remove') {
            mockData = { status: 'success' };
          }
        } else if (url.indexOf('report.php') !== -1) {
          mockData = { status: 'success' };
        } else {
          mockData = { status: 'success' };
        }
        
        resolve({ data: mockData });
      }, 300);
    });
  }
});
