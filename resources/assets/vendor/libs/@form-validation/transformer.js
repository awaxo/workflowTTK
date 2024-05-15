import { Transformer } from '@form-validation/plugin-transformer';

try {
  FormValidation.plugins.Transformer = Transformer;
} catch (e) {}

export { Transformer };
