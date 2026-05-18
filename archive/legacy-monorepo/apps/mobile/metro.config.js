const { getDefaultConfig } = require("expo/metro-config");
const path = require("path");

const projectRoot = __dirname;
const monorepoRoot = path.resolve(projectRoot, "../..");

const config = getDefaultConfig(projectRoot);

config.watchFolders = [monorepoRoot];

config.resolver.nodeModulesPaths = [
  path.resolve(projectRoot, "node_modules"),
  path.resolve(monorepoRoot, "node_modules"),
];

// Force react + jsx to always resolve from mobile's node_modules (19.1.0)
// so it matches the react-native-renderer version (also 19.1.0).
config.resolver.resolveRequest = (context, moduleName, platform) => {
  const reactModules = [
    "react",
    "react/jsx-runtime",
    "react/jsx-dev-runtime",
    "react/package.json",
  ];
  if (reactModules.includes(moduleName)) {
    return {
      filePath: require.resolve(moduleName, {
        paths: [path.resolve(projectRoot, "node_modules")],
      }),
      type: "sourceFile",
    };
  }
  return context.resolveRequest(context, moduleName, platform);
};

module.exports = config;
