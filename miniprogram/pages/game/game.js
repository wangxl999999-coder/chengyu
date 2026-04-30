const app = getApp();

Page({
  data: {
    level: 1,
    levelInfo: null,
    currentIdiomIndex: 0,
    idioms: [],
    currentIdiom: null,
    options: [],
    selectedWords: [],
    emptyPositions: [],
    filledPositions: {},
    isComplete: false,
    showResult: false,
    allIdioms: []
  },

  onLoad: function (options) {
    var level = parseInt(options.level) || 1;
    this.setData({ level: level });
    this.loadLevelData(level);
  },

  loadLevelData: function (level) {
    var that = this;
    wx.showLoading({ title: '加载中...' });
    
    app.request({
      url: '/level.php',
      method: 'GET',
      data: {
        level: level
      }
    }).then(function (res) {
      wx.hideLoading();
      if (res.data.status === 'success') {
        that.setData({
          levelInfo: res.data.data,
          idioms: res.data.data.idioms,
          allIdioms: res.data.data.idioms
        });
        that.initCurrentIdiom();
      } else {
        wx.showToast({
          title: '加载失败',
          icon: 'none'
        });
      }
    }).catch(function () {
      wx.hideLoading();
      wx.showToast({
        title: '网络错误',
        icon: 'none'
      });
    });
  },

  initCurrentIdiom: function () {
    var idioms = this.data.idioms;
    if (this.data.currentIdiomIndex >= idioms.length) {
      this.levelComplete();
      return;
    }

    var idiom = idioms[this.data.currentIdiomIndex];
    var characters = idiom.idiom.split('');
    var emptyCount = Math.min(2, characters.length - 1);
    var emptyPositions = this.getRandomEmptyPositions(characters.length, emptyCount);
    
    var correctWords = [];
    for (var i = 0; i < emptyPositions.length; i++) {
      correctWords.push(characters[emptyPositions[i]]);
    }

    var options = this.generateOptions(correctWords, idiom.difficulty);
    
    var initialFilled = {};
    emptyPositions.forEach(function (pos) {
      initialFilled[pos] = null;
    });

    this.setData({
      currentIdiom: idiom,
      characters: characters,
      emptyPositions: emptyPositions,
      filledPositions: initialFilled,
      options: options,
      correctWords: correctWords,
      isComplete: false,
      selectedWords: []
    });
  },

  getRandomEmptyPositions: function (length, count) {
    var positions = [];
    for (var i = 0; i < length; i++) {
      positions.push(i);
    }
    for (var i = positions.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var temp = positions[i];
      positions[i] = positions[j];
      positions[j] = temp;
    }
    return positions.slice(0, count);
  },

  generateOptions: function (correctWords, difficulty) {
    var distractors = ['的', '是', '在', '了', '不', '和', '有', '大', '这', '上',
                       '也', '人', '就', '出', '到', '说', '要', '去', '你', '会',
                       '着', '没有', '看', '好', '自己', '这', '那', '她', '它',
                       '小', '多', '么', '少', '让', '又', '把', '还', '比', '很'];
    
    var optionsCount = Math.min(12, 8 + difficulty);
    var options = correctWords.slice();
    
    while (options.length < optionsCount && distractors.length > 0) {
      var randomIndex = Math.floor(Math.random() * distractors.length);
      var word = distractors[randomIndex];
      if (options.indexOf(word) === -1 && correctWords.indexOf(word) === -1) {
        options.push(word);
      }
      distractors.splice(randomIndex, 1);
    }

    for (var i = options.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var temp = options[i];
      options[i] = options[j];
      options[j] = temp;
    }

    return options;
  },

  selectWord: function (e) {
    var word = e.currentTarget.dataset.word;
    var index = e.currentTarget.dataset.index;
    
    if (this.data.selectedWords.indexOf(index) !== -1) {
      return;
    }

    var emptyPositions = this.data.emptyPositions;
    var filledPositions = this.data.filledPositions;
    var nextEmpty = -1;

    for (var i = 0; i < emptyPositions.length; i++) {
      var pos = emptyPositions[i];
      if (filledPositions[pos] === null) {
        nextEmpty = pos;
        break;
      }
    }

    if (nextEmpty === -1) {
      return;
    }

    var correctWords = this.data.correctWords;
    var correctIndex = 0;
    for (var i = 0; i < emptyPositions.length; i++) {
      if (emptyPositions[i] === nextEmpty) {
        correctIndex = i;
        break;
      }
    }

    var isCorrect = word === correctWords[correctIndex];
    filledPositions[nextEmpty] = { word: word, correct: isCorrect };

    var selectedWords = this.data.selectedWords.slice();
    selectedWords.push(index);

    this.setData({
      filledPositions: filledPositions,
      selectedWords: selectedWords
    });

    if (isCorrect) {
      this.playSound('correct');
      this.checkAllFilled();
    } else {
      this.playSound('wrong');
      var that = this;
      setTimeout(function () {
        filledPositions[nextEmpty] = null;
        var idx = selectedWords.indexOf(index);
        if (idx !== -1) {
          selectedWords.splice(idx, 1);
        }
        that.setData({
          filledPositions: filledPositions,
          selectedWords: selectedWords
        });
      }, 500);
    }
  },

  checkAllFilled: function () {
    var filledPositions = this.data.filledPositions;
    var emptyPositions = this.data.emptyPositions;
    var allFilled = true;
    var allCorrect = true;

    for (var i = 0; i < emptyPositions.length; i++) {
      var pos = emptyPositions[i];
      if (filledPositions[pos] === null) {
        allFilled = false;
        break;
      }
      if (!filledPositions[pos].correct) {
        allCorrect = false;
      }
    }

    if (allFilled && allCorrect) {
      this.playSound('complete');
      var that = this;
      setTimeout(function () {
        that.nextIdiom();
      }, 1000);
    }
  },

  nextIdiom: function () {
    var nextIndex = this.data.currentIdiomIndex + 1;
    this.setData({
      currentIdiomIndex: nextIndex
    });
    this.initCurrentIdiom();
  },

  levelComplete: function () {
    var that = this;
    this.setData({
      isComplete: true,
      showResult: true
    });

    app.request({
      url: '/level.php',
      method: 'POST',
      data: {
        action: 'complete',
        level: this.data.level
      }
    }).then(function (res) {
      if (res.data.status === 'success') {
        app.globalData.userInfo = res.data.data;
      }
    });
  },

  goToResult: function () {
    wx.redirectTo({
      url: '/pages/result/result?level=' + this.data.level + 
           '&idioms=' + encodeURIComponent(JSON.stringify(this.data.allIdioms))
    });
  },

  playSound: function (type) {
    var soundUrls = {
      correct: '/sounds/correct.mp3',
      wrong: '/sounds/wrong.mp3',
      complete: '/sounds/complete.mp3'
    };

    try {
      const innerAudioContext = wx.createInnerAudioContext();
      innerAudioContext.src = soundUrls[type];
      innerAudioContext.play();
      innerAudioContext.onEnded(function () {
        innerAudioContext.destroy();
      });
    } catch (e) {
      console.log('播放音效失败:', e);
    }
  }
});
